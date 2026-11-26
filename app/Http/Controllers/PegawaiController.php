<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PegawaiController extends Controller
{
    public function index()
    {
        // Ambil daftar lengkap pegawai beserta relasi unit, jabatan, dan pola kerja
        $pegawaiList = DB::table('pegawai as p')
            ->leftJoin('master_unit_kerja as u', 'p.unit_kerja_aktif_id', '=', 'u.id')
            ->leftJoin('master_jabatan as j', 'p.jabatan_aktif_id', '=', 'j.id')
            ->leftJoin('master_pola_kerja as pk', 'p.pola_kerja_id', '=', 'pk.id')
            ->select(
                'p.*',
                DB::raw('CAST(p.status_kepegawaian AS TEXT) AS status_teks'),
                DB::raw('CAST(p.jenis_kelamin AS TEXT) AS gender_teks'),
                'u.nama_unit',
                'j.nama_jabatan',
                'j.kelas_jabatan',
                'j.pagu_tpp_dasar',
                'pk.nama_pola',
                'pk.target_jam_mingguan'
            )
            ->where('p.is_aktif', true)
            ->orderBy('p.nama_lengkap', 'asc')
            ->get();

        // Hitung jumlah dokumen e-File yang terverifikasi per pegawai
        $dokumenCounts = DB::table('dokumen_efile')
            ->select('pegawai_id', DB::raw('COUNT(*) as total_dok'))
            ->groupBy('pegawai_id')
            ->pluck('total_dok', 'pegawai_id');

        // Hitung statistik tab status kepegawaian
        $statistik = [
            'total'      => $pegawaiList->count(),
            'pns'        => $pegawaiList->filter(fn($p) => in_array($p->status_teks, ['PNS', 'CPNS']))->count(),
            'pppk_penuh' => $pegawaiList->filter(fn($p) => str_contains($p->status_teks, 'Penuh'))->count(),
            'pppk_paruh' => $pegawaiList->filter(fn($p) => str_contains($p->status_teks, 'Paruh'))->count(),
            'kontrak'    => $pegawaiList->filter(fn($p) => !in_array($p->status_teks, ['PNS', 'CPNS']) && !str_contains($p->status_teks, 'PPPK'))->count(),
        ];

        $unitKerjaList = DB::table('master_unit_kerja')->orderBy('nama_unit')->get();

        return view('pegawai.index', compact('pegawaiList', 'dokumenCounts', 'statistik', 'unitKerjaList'));
    }
}