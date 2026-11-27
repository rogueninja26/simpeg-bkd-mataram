<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\Models\RiwayatKontrakKerja;
use App\Models\Pegawai;
use App\Models\MasterGolongan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class KontrakKerjaController extends Controller
{
    public function index(Request $request)
    {
        $query = RiwayatKontrakKerja::with(['pegawai', 'golonganPppk']);

        if ((Auth::user()->role->nama_role ?? '') === 'Pegawai') {
            $query->where('pegawai_id', Auth::user()->pegawai->id ?? 0);
        }

        if ($request->filled('status')) {
            $query->where('status_evaluasi', $request->status);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_kontrak', $request->jenis);
        }

        $kontrakList  = $query->orderBy('tgl_akhir_kontrak', 'asc')->paginate(15);
        $pegawaiList = Pegawai::orderBy('nama_lengkap')->get(['id', 'nomor_induk', 'nama_lengkap']);
        $golonganList = MasterGolongan::orderBy('id')->get();

        return view('monitoring.kontrak.index', compact('kontrakList', 'pegawaiList', 'golonganList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pegawai_id'            => 'required|exists:pegawai,id',
            'jenis_kontrak'         => 'required|in:PPPK_PENUH,PPPK_PARUH,SPK_KONTRAK',
            'golongan_pppk_id'      => 'nullable|exists:master_golongan,id',
            'nomor_sk_spk'          => 'required|string|max:100',
            'tgl_penetapan'         => 'required|date',
            'tgl_mulai_kontrak'     => 'required|date',
            'tgl_akhir_kontrak'     => 'required|date|after_or_equal:tgl_mulai_kontrak',
            'gaji_atau_honor_pokok' => 'required|numeric|min:0',
            'status_evaluasi'       => 'required|in:Berjalan,Direkomendasikan_Perpanjang,Tidak_Diperpanjang,Alih_Status',
            'file_sk'               => 'nullable|file|mimes:pdf|max:5120',
        ]);

        DB::transaction(function () use ($request, $validated) {
            if ($request->hasFile('file_sk')) {
                $validated['file_kontrak_path'] = $request->file('file_sk')->store('efile/sk_kontrak', 'public');
            }

            // Nonaktifkan kontrak lama pegawai tersebut
            RiwayatKontrakKerja::where('pegawai_id', $validated['pegawai_id'])->update(['is_aktif' => false]);
            $validated['is_aktif'] = true;

            $record = RiwayatKontrakKerja::create($validated);

            $this->logAudit('INSERT', 'riwayat_kontrak_kerja', $record->id, null, $record->toArray());
        });

        return redirect()->route('monitoring.kontrak.index')->with('success', 'Data kontrak kerja berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $record = RiwayatKontrakKerja::findOrFail($id);
        $oldData = $record->toArray();

        $validated = $request->validate([
            'jenis_kontrak'         => 'required|in:PPPK_PENUH,PPPK_PARUH,SPK_KONTRAK',
            'golongan_pppk_id'      => 'nullable|exists:master_golongan,id',
            'nomor_sk_spk'          => 'required|string|max:100',
            'tgl_penetapan'         => 'required|date',
            'tgl_mulai_kontrak'     => 'required|date',
            'tgl_akhir_kontrak'     => 'required|date|after_or_equal:tgl_mulai_kontrak',
            'gaji_atau_honor_pokok' => 'required|numeric|min:0',
            'status_evaluasi'       => 'required|in:Berjalan,Direkomendasikan_Perpanjang,Tidak_Diperpanjang,Alih_Status',
            'file_sk'               => 'nullable|file|mimes:pdf|max:5120',
        ]);

        DB::transaction(function () use ($request, $record, $validated, $oldData) {
            if ($request->hasFile('file_sk')) {
                if ($record->file_kontrak_path) {
                    Storage::disk('public')->delete($record->file_kontrak_path);
                }
                $validated['file_kontrak_path'] = $request->file('file_sk')->store('efile/sk_kontrak', 'public');
            }

            $record->update($validated);

            $this->logAudit('UPDATE', 'riwayat_kontrak_kerja', $record->id, $oldData, $record->fresh()->toArray());
        });

        return redirect()->route('monitoring.kontrak.index')->with('success', 'Data kontrak kerja berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $record = RiwayatKontrakKerja::findOrFail($id);
        $oldData = $record->toArray();

        DB::transaction(function () use ($record, $oldData) {
            if ($record->file_kontrak_path) {
                Storage::disk('public')->delete($record->file_kontrak_path);
            }
            $record->delete();

            if ($oldData['is_aktif']) {
                $latest = RiwayatKontrakKerja::where('pegawai_id', $oldData['pegawai_id'])
                    ->orderBy('tgl_mulai_kontrak', 'desc')
                    ->first();
                if ($latest) {
                    $latest->update(['is_aktif' => true]);
                }
            }

            $this->logAudit('DELETE', 'riwayat_kontrak_kerja', $oldData['id'], $oldData, null);
        });

        return redirect()->route('monitoring.kontrak.index')->with('success', 'Data kontrak kerja dihapus.');
    }

        private function logAudit(string $action, string $table, $recordId, ?array $old, ?array $new): void
    {
        DB::table('audit_logs')->insert([
            'user_id'        => Auth::id(),
            'aksi'           => $action,
            'nama_tabel'     => $table,
            'record_id'      => $recordId,
            'data_lama_json' => $old ? json_encode($old) : null,
            'data_baru_json' => $new ? json_encode($new) : null,
            'ip_address'     => request()->ip(),
            'created_at'     => now(),
        ]);
    }
}