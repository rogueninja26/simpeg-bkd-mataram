<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use App\Models\PengajuanCutiIzin;
use App\Models\RekapPresensiBulanan;
use Illuminate\Database\Seeder;

class CutiPresensiSeeder extends Seeder
{
    public function run()
    {
        $pegawai = Pegawai::first();
        if (!$pegawai) {
            $this->command->warn('Tabel pegawai masih kosong. Jalankan seeder pegawai terlebih dahulu.');
            return;
        }

        // 1. Isi data awal rekap_presensi_bulanan (November 2026)
        RekapPresensiBulanan::updateOrCreate(
            [
                'pegawai_id' => $pegawai->id,
                'bulan'      => 11,
                'tahun'      => 2026,
            ],
            [
                'wajib_hari_kerja'      => 21,
                'jumlah_hadir'          => 20,
                'jumlah_cuti_izin'      => 0,
                'jumlah_dinas_luar'     => 1,
                'total_terlambat_menit' => 5,
                'tanpa_keterangan'      => 0,
                'persentase_disiplin'   => 100.00,
            ]
        );

        // 2. Isi 1 contoh pengajuan_cuti_izin berstatus Diajukan
        PengajuanCutiIzin::create([
            'pegawai_id'         => $pegawai->id,
            'atasan_id'          => null,
            'jenis_cuti_izin'    => in_array($pegawai->status_kepegawaian, ['KONTRAK', 'PPPK_PARUH']) ? 'Izin_Kontrak' : 'Cuti_Tahunan',
            'tgl_mulai'          => '2026-11-25',
            'tgl_selesai'        => '2026-11-26',
            'jumlah_hari_kerja'  => 2,
            'alasan'             => 'Keperluan keluarga mendesak di Mataram',
            'alamat_selama_cuti' => 'Kota Mataram',
            'status_approval'    => 'Diajukan',
        ]);
    }
}