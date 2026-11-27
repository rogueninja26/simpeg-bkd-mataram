<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\User;
use App\Models\Role;
use App\Models\DokumenEfile;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PegawaiController extends Controller
{
    private function catatAudit(string $aksi, string $namaTabel, int $recordId, ?array $dataLama, ?array $dataBaru, Request $request): void
    {
        AuditLog::create([
            'user_id'        => Auth::id(),
            'aksi'           => $aksi,
            'nama_tabel'     => $namaTabel,
            'record_id'      => $recordId,
            'data_lama_json' => $dataLama,
            'data_baru_json' => $dataBaru,
            'ip_address'     => $request->ip(),
            'created_at'     => now(),
        ]);
    }

    public function index()
    {
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

        $allDokumen = DB::table('dokumen_efile as d')
            ->leftJoin('users as u', 'd.verified_by', '=', 'u.id')
            ->leftJoin('pegawai as vp', 'u.id', '=', 'vp.user_id')
            ->select(
                'd.id',
                'd.pegawai_id',
                'd.kategori_dokumen',
                'd.nama_file',
                'd.file_path',
                DB::raw('CAST(d.status_verifikasi AS TEXT) AS status_verifikasi'),
                'd.catatan_operator',
                'd.uploaded_at',
                DB::raw("COALESCE(vp.nama_lengkap, u.username_login, '-') AS verifier_nama")
            )
            ->orderBy('d.uploaded_at', 'desc')
            ->get()
            ->map(function ($doc) {
                $doc->file_url = asset('storage/' . $doc->file_path);
                return $doc;
            });

        $dokumenByPegawai = $allDokumen->groupBy('pegawai_id');
        $dokumenCounts    = $allDokumen->groupBy('pegawai_id')->map->count();

        $statistik = [
            'total'      => $pegawaiList->count(),
            'pns'        => $pegawaiList->filter(fn($p) => in_array($p->status_teks, ['PNS', 'CPNS']))->count(),
            'pppk_penuh' => $pegawaiList->filter(fn($p) => str_contains($p->status_teks, 'Penuh') || $p->status_teks === 'PPPK_PENUH')->count(),
            'pppk_paruh' => $pegawaiList->filter(fn($p) => str_contains($p->status_teks, 'Paruh') || $p->status_teks === 'PPPK_PARUH')->count(),
            'kontrak'    => $pegawaiList->filter(fn($p) => !in_array($p->status_teks, ['PNS', 'CPNS']) && !str_contains($p->status_teks, 'PPPK'))->count(),
        ];

        $unitKerjaList = DB::table('master_unit_kerja')->orderBy('nama_unit')->get();
        $jabatanList   = DB::table('master_jabatan')->orderBy('nama_jabatan')->get();
        $polaKerjaList = DB::table('master_pola_kerja')->orderBy('nama_pola')->get();

        return view('pegawai.index', compact(
            'pegawaiList',
            'dokumenCounts',
            'dokumenByPegawai',
            'statistik',
            'unitKerjaList',
            'jabatanList',
            'polaKerjaList'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_induk'          => 'required|string|max:30|unique:pegawai,nomor_induk|unique:users,username_login',
            'nama_lengkap'         => 'required|string|max:150',
            'gelar_depan'          => 'nullable|string|max:30',
            'gelar_belakang'       => 'nullable|string|max:50',
            'nik'                  => 'required|string|size:16|unique:pegawai,nik',
            'npwp'                 => 'nullable|string|max:25',
            'tempat_lahir'         => 'required|string|max:100',
            'tgl_lahir'            => 'required|date',
            'tmt_awal_masuk'       => 'required|date',
            'jenis_kelamin'        => 'required|in:L,P',
            'status_kepegawaian'   => 'required|in:PNS,CPNS,PPPK_PENUH,PPPK_PARUH,KONTRAK',
            'unit_kerja_aktif_id'  => 'required|exists:master_unit_kerja,id',
            'jabatan_aktif_id'     => 'required|exists:master_jabatan,id',
            'pola_kerja_id'        => 'required|exists:master_pola_kerja,id',
            'no_hp'                => 'nullable|string|max:20',
            'email'                => 'nullable|email|max:100',
            'alamat_domisili'      => 'nullable|string',
        ], [
            'nomor_induk.unique' => 'NIP / Nomor Induk sudah terdaftar pada data pegawai atau akun pengguna.',
            'nik.unique'         => 'NIK 16 digit sudah terdaftar pada data pegawai lain.',
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {
                $rolePegawai = Role::where('nama_role', 'Pegawai')->firstOrFail();

                $user = User::create([
                    'username_login' => $validated['nomor_induk'],
                    'password'       => Hash::make($validated['nomor_induk']),
                    'role_id'        => $rolePegawai->id,
                    'is_active'      => true,
                ]);

                $validated['user_id']  = $user->id;
                $validated['is_aktif'] = true;

                $pegawai = Pegawai::create($validated);

                $this->catatAudit('CREATE_PEGAWAI', 'pegawai', $pegawai->id, null, $pegawai->toArray(), $request);
            });

            return redirect()
                ->route('pegawai.index')
                ->with('success', 'Data pegawai baru dan akun login (password default: NIP/Nomor Induk) berhasil dibuat.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data pegawai: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $pegawai = Pegawai::findOrFail($id);

        $validated = $request->validate([
            'nomor_induk'          => 'required|string|max:30|unique:pegawai,nomor_induk,' . $pegawai->id . '|unique:users,username_login,' . $pegawai->user_id,
            'nama_lengkap'         => 'required|string|max:150',
            'gelar_depan'          => 'nullable|string|max:30',
            'gelar_belakang'       => 'nullable|string|max:50',
            'nik'                  => 'required|string|size:16|unique:pegawai,nik,' . $pegawai->id,
            'npwp'                 => 'nullable|string|max:25',
            'tempat_lahir'         => 'required|string|max:100',
            'tgl_lahir'            => 'required|date',
            'tmt_awal_masuk'       => 'required|date',
            'jenis_kelamin'        => 'required|in:L,P',
            'status_kepegawaian'   => 'required|in:PNS,CPNS,PPPK_PENUH,PPPK_PARUH,KONTRAK',
            'unit_kerja_aktif_id'  => 'required|exists:master_unit_kerja,id',
            'jabatan_aktif_id'     => 'required|exists:master_jabatan,id',
            'pola_kerja_id'        => 'required|exists:master_pola_kerja,id',
            'no_hp'                => 'nullable|string|max:20',
            'email'                => 'nullable|email|max:100',
            'alamat_domisili'      => 'nullable|string',
        ], [
            'nomor_induk.unique' => 'NIP / Nomor Induk tersebut sudah digunakan oleh pegawai lain.',
            'nik.unique'         => 'NIK 16 digit tersebut sudah digunakan oleh pegawai lain.',
        ]);

        try {
            DB::transaction(function () use ($pegawai, $validated, $request) {
                $dataLama = $pegawai->toArray();
                $pegawai->update($validated);

                if ($pegawai->user && $pegawai->user->username_login !== $validated['nomor_induk']) {
                    $pegawai->user->update([
                        'username_login' => $validated['nomor_induk'],
                    ]);
                }

                $this->catatAudit('UPDATE_PEGAWAI', 'pegawai', $pegawai->id, $dataLama, $pegawai->fresh()->toArray(), $request);
            });

            return redirect()
                ->route('pegawai.index')
                ->with('success', 'Profil pegawai berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui profil pegawai: ' . $e->getMessage());
        }
    }

    public function uploadEfile(Request $request, $pegawai_id)
    {
        $pegawai = Pegawai::findOrFail($pegawai_id);

        $request->validate([
            'kategori_dokumen' => 'required|string|max:50',
            'file_pdf'         => 'required|file|mimes:pdf|max:5120',
        ], [
            'file_pdf.mimes' => 'Berkas e-File harus berformat PDF.',
            'file_pdf.max'   => 'Ukuran berkas PDF maksimal 5 MB.',
        ]);

        try {
            $file = $request->file('file_pdf');
            $namaAsli = $file->getClientOriginalName();
            $path = $file->store('efile/' . $pegawai->nomor_induk, 'public');

            $dokumen = DokumenEfile::create([
                'pegawai_id'        => $pegawai->id,
                'kategori_dokumen'  => $request->kategori_dokumen,
                'nama_file'         => $namaAsli,
                'file_path'         => $path,
                'status_verifikasi' => 'Pending',
                'uploaded_at'       => now(),
            ]);

            $this->catatAudit('UPLOAD_EFILE', 'dokumen_efile', $dokumen->id, null, $dokumen->toArray(), $request);

            return redirect()
                ->route('pegawai.index')
                ->with('success', 'Dokumen "' . $namaAsli . '" berhasil diunggah dan menunggu verifikasi.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengunggah dokumen e-File: ' . $e->getMessage());
        }
    }

    public function verifikasiEfile(Request $request, $id)
    {
        $dokumen = DokumenEfile::findOrFail($id);

        $request->validate([
            'status_verifikasi' => 'required|in:Valid,Ditolak,Pending',
            'catatan_operator'  => 'nullable|string|max:500',
        ]);

        try {
            $dataLama = $dokumen->toArray();

            $dokumen->update([
                'status_verifikasi' => $request->status_verifikasi,
                'catatan_operator'  => $request->catatan_operator,
                'verified_by'       => Auth::id(),
            ]);

            $this->catatAudit('VERIFIKASI_EFILE', 'dokumen_efile', $dokumen->id, $dataLama, $dokumen->fresh()->toArray(), $request);

            return redirect()
                ->route('pegawai.index')
                ->with('success', 'Status dokumen "' . $dokumen->kategori_dokumen . '" diperbarui menjadi ' . $request->status_verifikasi . '.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui status verifikasi: ' . $e->getMessage());
        }
    }
}