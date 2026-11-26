<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Contoh pengambilan data agregat (sesuaikan nama tabel & kolom dengan skema DB Anda)
        // Jika tabel bernama 'pegawai' dan kolom status bernama 'status_kepegawaian':
        /*
        $stats = [
            'total'   => DB::table('pegawai')->count(),
            'pns'     => DB::table('pegawai')->whereIn('status_kepegawaian', ['PNS', 'CPNS'])->count(),
            'pppk'    => DB::table('pegawai')->whereIn('status_kepegawaian', ['PPPK Penuh Waktu', 'PPPK Paruh Waktu'])->count(),
            'kontrak' => DB::table('pegawai')->where('status_kepegawaian', 'Tenaga Kontrak')->count(),
        ];
        */

        // Data sementara agar tampilan langsung berjalan sebelum disesuaikan nama kolomnya
        $stats = [
            'total'   => 184,
            'pns'     => 76,
            'pppk'    => 70,
            'kontrak' => 38,
        ];

        $bezettingUnit = [
            ['nama' => 'Sekretariat & Subbag Umum Kepegawaian', 'pns' => 22, 'pppk' => 18, 'kontrak' => 10, 'total' => 50],
            ['nama' => 'Bidang Pendataan & Penetapan Pajak', 'pns' => 18, 'pppk' => 20, 'kontrak' => 12, 'total' => 50],
            ['nama' => 'Bidang Penagihan & Keberatan', 'pns' => 16, 'pppk' => 15, 'kontrak' => 8, 'total' => 39],
            ['nama' => 'Bidang Anggaran & Perbendaharaan', 'pns' => 20, 'pppk' => 17, 'kontrak' => 8, 'total' => 45],
        ];

        return view('dashboard.index', compact('stats', 'bezettingUnit'));
    }
}