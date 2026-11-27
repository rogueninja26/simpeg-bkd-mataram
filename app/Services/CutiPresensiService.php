<?php

namespace App\Services;

use App\Models\PengajuanCutiIzin;
use App\Models\RekapPresensiBulanan;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class CutiPresensiService
{
    /**
     * Hitung hari kerja (Senin - Jumat) di antara dua tanggal
     */
    public function hitungHariKerja(string $tglMulai, string $tglSelesai): int
    {
        $period = CarbonPeriod::create($tglMulai, $tglSelesai);
        $hariKerja = 0;

        foreach ($period as $date) {
            if (!$date->isWeekend()) {
                $hariKerja++;
            }
        }

        return $hariKerja;
    }

    /**
     * Sinkronisasi approval cuti ke rekap_presensi_bulanan
     */
    public function sinkronisasiKeRekapBulanan(PengajuanCutiIzin $cuti): void
    {
        $period = CarbonPeriod::create($cuti->tgl_mulai, $cuti->tgl_selesai);
        $rekapPerBulan = [];

        foreach ($period as $date) {
            if (!$date->isWeekend()) {
                $key = $date->year . '-' . $date->month;
                if (!isset($rekapPerBulan[$key])) {
                    $rekapPerBulan[$key] = [
                        'tahun' => $date->year,
                        'bulan' => $date->month,
                        'hari'  => 0,
                    ];
                }
                $rekapPerBulan[$key]['hari']++;
            }
        }

        foreach ($rekapPerBulan as $item) {
            $rekap = RekapPresensiBulanan::firstOrNew([
                'pegawai_id' => $cuti->pegawai_id,
                'bulan'      => $item['bulan'],
                'tahun'      => $item['tahun'],
            ]);

            if (!$rekap->exists) {
                $rekap->wajib_hari_kerja      = 22; // Default hari kerja bulanan BKD
                $rekap->jumlah_hadir          = 22;
                $rekap->jumlah_cuti_izin      = 0;
                $rekap->jumlah_dinas_luar     = 0;
                $rekap->total_terlambat_menit = 0;
                $rekap->tanpa_keterangan      = 0;
                $rekap->persentase_disiplin   = 100.00;
            }

            // Tambahkan kuota cuti/izin yang disetujui & kurangi hadir fisik
            $rekap->jumlah_cuti_izin += $item['hari'];
            $rekap->jumlah_hadir = max(0, $rekap->jumlah_hadir - $item['hari']);

            // Hitung ulang persentase disiplin (Cuti resmi tidak memotong disiplin)
            $totalTerpenuhi = $rekap->jumlah_hadir + $rekap->jumlah_cuti_izin + $rekap->jumlah_dinas_luar;
            $persen = $rekap->wajib_hari_kerja > 0
                ? min(100, round(($totalTerpenuhi / $rekap->wajib_hari_kerja) * 100, 2))
                : 100.00;

            $rekap->persentase_disiplin = $persen;
            $rekap->save();
        }
    }

    /**
     * Catat perubahan ke tabel audit_logs (JSONB PostgreSQL)
     */
    public function catatAuditLog(?int $userId, string $aksi, string $tabel, int $recordId, ?array $lama, ?array $baru, ?string $ip): void
    {
        DB::table('audit_logs')->insert([
            'user_id'        => $userId,
            'aksi'           => $aksi,
            'nama_tabel'     => $tabel,
            'record_id'      => $recordId,
            'data_lama_json' => $lama ? json_encode($lama) : null,
            'data_baru_json' => $baru ? json_encode($baru) : null,
            'ip_address'     => $ip,
            'created_at'     => now(),
        ]);
    }
}