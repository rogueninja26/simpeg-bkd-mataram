<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\PengajuanCutiIzin;
use App\Models\RekapPresensiBulanan;
use App\Services\CutiPresensiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CutiPresensiController extends Controller
{
    protected $service;

    public function __construct(CutiPresensiService $service)
    {
        $this->service = $service;
    }

    /**
     * Halaman Utama Modul Cuti, Izin & Rekap Presensi Bulanan
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $pegawaiLogin = Pegawai::where('user_id', $user->id)->first();
        $roleName = $user->role->nama_role ?? 'Pegawai';

        $bulan = (int) $request->get('bulan', date('n'));
        $tahun = (int) $request->get('tahun', date('Y'));

        // Query Pengajuan Cuti/Izin
        $queryCuti = PengajuanCutiIzin::with(['pegawai.unitKerja', 'pegawai.jabatan', 'atasan'])
            ->orderByDesc('id');

        // Jika role Pegawai biasa, hanya tampilkan pengajuan miliknya
        if (!in_array($roleName, ['Superadmin', 'Admin_Kepegawaian', 'Operator', 'Pimpinan', 'Kaban'])) {
            $queryCuti->where('pegawai_id', $pegawaiLogin->id ?? 0);
        }

        $daftarCuti = $queryCuti->paginate(10, ['*'], 'cuti_page');

        // Query Rekap Presensi Bulanan
        $queryPresensi = RekapPresensiBulanan::with(['pegawai.unitKerja'])
            ->where('bulan', $bulan)
            ->where('tahun', $tahun);

        if (!in_array($roleName, ['Superadmin', 'Admin_Kepegawaian', 'Operator', 'Pimpinan', 'Kaban'])) {
            $queryPresensi->where('pegawai_id', $pegawaiLogin->id ?? 0);
        }

        $rekapPresensi = $queryPresensi->get();

        // Daftar atasan untuk dropdown pengajuan mandiri
        $daftarAtasan = Pegawai::where('is_aktif', true)
            ->whereHas('jabatan', function ($q) {
                $q->where('jenis_jabatan', 'Struktural');
            })
            ->orderBy('nama_lengkap')
            ->get();

        return view('cuti_presensi.index', compact(
            'pegawaiLogin',
            'roleName',
            'daftarCuti',
            'rekapPresensi',
            'daftarAtasan',
            'bulan',
            'tahun'
        ));
    }

    /**
     * Simpan Pengajuan Cuti / Izin Mandiri oleh Pegawai
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $pegawai = Pegawai::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'atasan_id'          => 'nullable|exists:pegawai,id',
            'jenis_cuti_izin'    => ['required', Rule::in([
                'Cuti_Tahunan',
                'Cuti_Sakit',
                'Cuti_Melahirkan',
                'Cuti_Alasan_Penting',
                'Cuti_Besar',
                'Izin_Kontrak',
            ])],
            'tgl_mulai'          => 'required|date',
            'tgl_selesai'        => 'required|date|after_or_equal:tgl_mulai',
            'alasan'             => 'required|string|max:1000',
            'alamat_selama_cuti' => 'nullable|string|max:500',
            'lampiran_bukti'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // Validasi khusus status_kepegawaian sesuai ENUM database
        if (in_array($pegawai->status_kepegawaian, ['KONTRAK', 'PPPK_PARUH']) && $validated['jenis_cuti_izin'] !== 'Izin_Kontrak') {
            return back()->withErrors(['jenis_cuti_izin' => 'Pegawai Kontrak / PPPK Paruh Waktu hanya dapat memilih jenis Izin Kontrak.'])->withInput();
        }

        $jumlahHari = $this->service->hitungHariKerja($validated['tgl_mulai'], $validated['tgl_selesai']);
        if ($jumlahHari <= 0) {
            return back()->withErrors(['tgl_mulai' => 'Rentang tanggal yang dipilih tidak memiliki hari kerja aktif.'])->withInput();
        }

        $lampiranPath = null;
        if ($request->hasFile('lampiran_bukti')) {
            $lampiranPath = $request->file('lampiran_bukti')->store('dokumen_cuti/lampiran', 'public');
        }

        DB::transaction(function () use ($pegawai, $validated, $jumlahHari, $lampiranPath, $user, $request) {
            $cuti = PengajuanCutiIzin::create([
                'pegawai_id'          => $pegawai->id,
                'atasan_id'           => $validated['atasan_id'] ?? null,
                'jenis_cuti_izin'     => $validated['jenis_cuti_izin'],
                'tgl_mulai'           => $validated['tgl_mulai'],
                'tgl_selesai'         => $validated['tgl_selesai'],
                'jumlah_hari_kerja'   => $jumlahHari,
                'alasan'              => $validated['alasan'],
                'alamat_selama_cuti'  => $validated['alamat_selama_cuti'] ?? null,
                'lampiran_bukti_path' => $lampiranPath,
                'status_approval'     => 'Diajukan',
            ]);

            $this->service->catatAuditLog(
                $user->id,
                'INSERT_PENGAJUAN_CUTI',
                'pengajuan_cuti_izin',
                $cuti->id,
                null,
                $cuti->toArray(),
                $request->ip()
            );
        });

        return redirect()->route('cuti-presensi.index')
            ->with('success', 'Pengajuan cuti/izin berhasil dikirim dan menunggu persetujuan.');
    }

    /**
     * Proses Approval / Penolakan oleh Atasan, Operator Kepegawaian, atau Kaban
     */
    public function prosesApproval(Request $request, int $id)
    {
        $user = Auth::user();
        $cuti = PengajuanCutiIzin::findOrFail($id);

        $validated = $request->validate([
            'status_approval'   => ['required', Rule::in([
                'Disetujui_Atasan',
                'Disetujui_Kaban',
                'Ditolak',
            ])],
            'catatan_penolakan' => 'required_if:status_approval,Ditolak|nullable|string|max:500',
            'nomor_surat'       => 'nullable|string|max:100',
            'file_surat_tte'    => 'nullable|file|mimes:pdf|max:3072',
        ]);

        // Cegah pemrosesan ulang jika sudah final
        if (in_array($cuti->status_approval, ['Disetujui_Kaban', 'Ditolak'])) {
            return back()->with('error', 'Pengajuan ini sudah berstatus final (' . $cuti->status_approval . ').');
        }

        DB::transaction(function () use ($cuti, $validated, $request, $user) {
            $dataLama = $cuti->toArray();

            if ($request->hasFile('file_surat_tte')) {
                $cuti->file_surat_tte_path = $request->file('file_surat_tte')->store('dokumen_cuti/surat_tte', 'public');
            }

            $cuti->status_approval   = $validated['status_approval'];
            $cuti->catatan_penolakan = $validated['status_approval'] === 'Ditolak' ? $validated['catatan_penolakan'] : null;
            if (!empty($validated['nomor_surat'])) {
                $cuti->nomor_surat = $validated['nomor_surat'];
            }
            $cuti->save();

            // Jika disetujui final (Disetujui_Kaban), otomatis update rekap_presensi_bulanan
            if ($cuti->status_approval === 'Disetujui_Kaban') {
                $this->service->sinkronisasiKeRekapBulanan($cuti);
            }

            $this->service->catatAuditLog(
                $user->id,
                'APPROVAL_CUTI_' . strtoupper($cuti->status_approval),
                'pengajuan_cuti_izin',
                $cuti->id,
                $dataLama,
                $cuti->toArray(),
                $request->ip()
            );
        });

        return redirect()->route('cuti-presensi.index')
            ->with('success', 'Status pengajuan berhasil diperbarui menjadi ' . str_replace('_', ' ', $validated['status_approval']) . '.');
    }

    /**
     * Input / Generate Rekap Presensi Bulanan oleh Operator Kepegawaian
     */
    public function storeRekapPresensi(Request $request)
    {
        $validated = $request->validate([
            'pegawai_id'            => 'required|exists:pegawai,id',
            'bulan'                 => 'required|integer|min:1|max:12',
            'tahun'                 => 'required|integer|min:2024|max:2035',
            'wajib_hari_kerja'      => 'required|integer|min:1|max:31',
            'jumlah_hadir'          => 'required|integer|min:0|max:31',
            'jumlah_dinas_luar'     => 'nullable|integer|min:0|max:31',
            'total_terlambat_menit' => 'nullable|integer|min:0',
            'tanpa_keterangan'      => 'nullable|integer|min:0|max:31',
        ]);

        // Hitung otomatis cuti yang sudah berstatus Disetujui_Kaban di bulan & tahun terkait
        $cutiApproved = PengajuanCutiIzin::where('pegawai_id', $validated['pegawai_id'])
            ->where('status_approval', 'Disetujui_Kaban')
            ->whereYear('tgl_mulai', $validated['tahun'])
            ->whereMonth('tgl_mulai', $validated['bulan'])
            ->sum('jumlah_hari_kerja');

        $dinasLuar = $validated['jumlah_dinas_luar'] ?? 0;
        $terlambat = $validated['total_terlambat_menit'] ?? 0;
        $alpa      = $validated['tanpa_keterangan'] ?? 0;

        $totalTerpenuhi = $validated['jumlah_hadir'] + $cutiApproved + $dinasLuar;
        $persenDisiplin = $validated['wajib_hari_kerja'] > 0
            ? min(100, round(($totalTerpenuhi / $validated['wajib_hari_kerja']) * 100, 2))
            : 100.00;

        RekapPresensiBulanan::updateOrCreate(
            [
                'pegawai_id' => $validated['pegawai_id'],
                'bulan'      => $validated['bulan'],
                'tahun'      => $validated['tahun'],
            ],
            [
                'wajib_hari_kerja'      => $validated['wajib_hari_kerja'],
                'jumlah_hadir'          => $validated['jumlah_hadir'],
                'jumlah_cuti_izin'      => $cutiApproved,
                'jumlah_dinas_luar'     => $dinasLuar,
                'total_terlambat_menit' => $terlambat,
                'tanpa_keterangan'      => $alpa,
                'persentase_disiplin'   => $persenDisiplin,
            ]
        );

        return back()->with('success', 'Rekap presensi bulanan berhasil disimpan dan disinkronkan dengan data cuti.');
    }
}