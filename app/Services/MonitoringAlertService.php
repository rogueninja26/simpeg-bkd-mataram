<?php

namespace App\Services;

use App\Models\RiwayatKontrakKerja;
use App\Models\RiwayatKgb;
use App\Models\RiwayatPangkat;
use Carbon\Carbon;

class MonitoringAlertService
{
    public static function getAlerts(): array
    {
        $today = Carbon::today();

        $kontrak = RiwayatKontrakKerja::with('pegawai')
            ->akanHabis(90)
            ->orderBy('tgl_akhir_kontrak', 'asc')
            ->get()
            ->map(function ($item) use ($today) {
                return [
                    'kategori'  => 'KONTRAK',
                    'pegawai'   => $item->pegawai->nama_lengkap ?? '-',
                    'nip'       => $item->pegawai->nomor_induk ?? '-',
                    'detail'    => $item->jenis_kontrak . ' berakhir ' . $item->tgl_akhir_kontrak->format('d/m/Y'),
                    'sisa_hari' => $today->diffInDays($item->tgl_akhir_kontrak, false),
                    'url'       => route('monitoring.kontrak.index', ['highlight' => $item->id]),
                ];
            });

        $kgb = RiwayatKgb::with('pegawai')
            ->jatuhTempo(60)
            ->orderBy('tmt_kgb_berikutnya', 'asc')
            ->get()
            ->map(function ($item) use ($today) {
                return [
                    'kategori'  => 'KGB',
                    'pegawai'   => $item->pegawai->nama_lengkap ?? '-',
                    'nip'       => $item->pegawai->nomor_induk ?? '-',
                    'detail'    => 'Jatuh tempo KGB ' . $item->tmt_kgb_berikutnya->format('d/m/Y'),
                    'sisa_hari' => $today->diffInDays($item->tmt_kgb_berikutnya, false),
                    'url'       => route('monitoring.kgb.index', ['highlight' => $item->id]),
                ];
            });

        $kp = RiwayatPangkat::with(['pegawai', 'golongan'])
            ->mendekatiProyeksi(90)
            ->orderBy('proyeksi_kp_berikutnya', 'asc')
            ->get()
            ->map(function ($item) use ($today) {
                return [
                    'kategori'  => 'KP',
                    'pegawai'   => $item->pegawai->nama_lengkap ?? '-',
                    'nip'       => $item->pegawai->nomor_induk ?? '-',
                    'detail'    => 'Proyeksi KP ' . $item->proyeksi_kp_berikutnya->format('d/m/Y'),
                    'sisa_hari' => $today->diffInDays($item->proyeksi_kp_berikutnya, false),
                    'url'       => route('monitoring.kp.index', ['highlight' => $item->id]),
                ];
            });

        $all = $kontrak->concat($kgb)->concat($kp)->sortBy('sisa_hari')->values();

        return [
            'total'   => $all->count(),
            'kontrak' => $kontrak->count(),
            'kgb'     => $kgb->count(),
            'kp'      => $kp->count(),
            'items'   => $all,
        ];
    }
}