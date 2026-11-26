<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Hitung 4 Kartu KPI Utama dari pegawai yang berstatus aktif (is_aktif = true)
        $pegawaiAktif = DB::table('pegawai')
            ->select(DB::raw('CAST(status_kepegawaian AS TEXT) AS status_teks'))
            ->where('is_aktif', true)
            ->get();

        $stats = [
            'total'   => $pegawaiAktif->count(),
            'pns'     => $pegawaiAktif->filter(fn($p) => in_array($p->status_teks, ['PNS', 'CPNS']))->count(),
            'pppk'    => $pegawaiAktif->filter(fn($p) => str_contains($p->status_teks, 'PPPK'))->count(),
            'kontrak' => $pegawaiAktif->filter(fn($p) => !in_array($p->status_teks, ['PNS', 'CPNS']) && !str_contains($p->status_teks, 'PPPK'))->count(),
        ];

        // 2. Hitung Matriks Bezetting SDM per Unit Kerja dari master_unit_kerja
        $units = DB::table('master_unit_kerja')->orderBy('id')->get();
        $bezettingUnit = [];

        foreach ($units as $unit) {
            $pegawaiUnit = DB::table('pegawai')
                ->select(DB::raw('CAST(status_kepegawaian AS TEXT) AS status_teks'))
                ->where('unit_kerja_aktif_id', $unit->id)
                ->where('is_aktif', true)
                ->get();

            $pnsCount     = $pegawaiUnit->filter(fn($p) => in_array($p->status_teks, ['PNS', 'CPNS']))->count();
            $pppkCount    = $pegawaiUnit->filter(fn($p) => str_contains($p->status_teks, 'PPPK'))->count();
            $kontrakCount = $pegawaiUnit->filter(fn($p) => !in_array($p->status_teks, ['PNS', 'CPNS']) && !str_contains($p->status_teks, 'PPPK'))->count();
            $totalUnit    = $pegawaiUnit->count();

            $bezettingUnit[] = [
                'nama'    => $unit->nama_unit,
                'pns'     => $pnsCount,
                'pppk'    => $pppkCount,
                'kontrak' => $kontrakCount,
                'total'   => $totalUnit,
            ];
        }

        return view('dashboard.index', compact('stats', 'bezettingUnit'));
    }
}