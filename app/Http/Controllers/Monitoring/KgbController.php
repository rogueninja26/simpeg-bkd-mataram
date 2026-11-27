<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\Models\RiwayatKgb;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KgbController extends Controller
{
    public function index(Request $request)
    {
        $query = RiwayatKgb::with('pegawai');

        if ((Auth::user()->role->nama_role ?? '') === 'Pegawai') {
            $query->where('pegawai_id', Auth::user()->pegawai->id ?? 0);
        }

        if ($request->get('filter') === 'jatuh_tempo') {
            $query->jatuhTempo(60);
        }

        $kgbList = $query->orderBy('tmt_kgb_berikutnya', 'asc')->paginate(15);
        $pegawaiList = Pegawai::orderBy('nama_lengkap')->get(['id', 'nomor_induk', 'nama_lengkap']);

        return view('monitoring.kgb.index', compact('kgbList', 'pegawaiList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pegawai_id'         => 'required|exists:pegawai,id',
            'nomor_sk'           => 'required|string|max:100',
            'tgl_sk'             => 'required|date',
            'tmt_kgb'            => 'required|date',
            'tmt_kgb_berikutnya' => 'nullable|date|after:tmt_kgb',
            'gaji_pokok_lama'    => 'required|numeric|min:0',
            'gaji_pokok_baru'    => 'required|numeric|min:0',
            'mkg_tahun'          => 'required|integer|min:0',
            'mkg_bulan'          => 'required|integer|min:0|max:11',
            'file_sk'            => 'nullable|file|mimes:pdf|max:5120',
        ]);

        if (empty($validated['tmt_kgb_berikutnya'])) {
            $validated['tmt_kgb_berikutnya'] = Carbon::parse($validated['tmt_kgb'])->addYears(2)->format('Y-m-d');
        }

        DB::transaction(function () use ($request, $validated) {
            if ($request->hasFile('file_sk')) {
                $validated['file_sk_path'] = $request->file('file_sk')->store('efile/sk_kgb', 'public');
            }

            RiwayatKgb::where('pegawai_id', $validated['pegawai_id'])->update(['is_aktif' => false]);
            $validated['is_aktif'] = true;

            $record = RiwayatKgb::create($validated);

            $this->logAudit('INSERT', 'riwayat_kgb', $record->id, null, $record->toArray());
        });

        return redirect()->route('monitoring.kgb.index')->with('success', 'Riwayat KGB berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $record = RiwayatKgb::findOrFail($id);
        $oldData = $record->toArray();

        $validated = $request->validate([
            'nomor_sk'           => 'required|string|max:100',
            'tgl_sk'             => 'required|date',
            'tmt_kgb'            => 'required|date',
            'tmt_kgb_berikutnya' => 'required|date|after:tmt_kgb',
            'gaji_pokok_lama'    => 'required|numeric|min:0',
            'gaji_pokok_baru'    => 'required|numeric|min:0',
            'mkg_tahun'          => 'required|integer|min:0',
            'mkg_bulan'          => 'required|integer|min:0|max:11',
            'file_sk'            => 'nullable|file|mimes:pdf|max:5120',
        ]);

        DB::transaction(function () use ($request, $record, $validated, $oldData) {
            if ($request->hasFile('file_sk')) {
                if ($record->file_sk_path) {
                    Storage::disk('public')->delete($record->file_sk_path);
                }
                $validated['file_sk_path'] = $request->file('file_sk')->store('efile/sk_kgb', 'public');
            }

            $record->update($validated);

            $this->logAudit('UPDATE', 'riwayat_kgb', $record->id, $oldData, $record->fresh()->toArray());
        });

        return redirect()->route('monitoring.kgb.index')->with('success', 'Riwayat KGB berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $record = RiwayatKgb::findOrFail($id);
        $oldData = $record->toArray();

        DB::transaction(function () use ($record, $oldData) {
            if ($record->file_sk_path) {
                Storage::disk('public')->delete($record->file_sk_path);
            }
            $record->delete();

            if ($oldData['is_aktif']) {
                $latest = RiwayatKgb::where('pegawai_id', $oldData['pegawai_id'])
                    ->orderBy('tmt_kgb', 'desc')
                    ->first();
                if ($latest) {
                    $latest->update(['is_aktif' => true]);
                }
            }

            $this->logAudit('DELETE', 'riwayat_kgb', $oldData['id'], $oldData, null);
        });

        return redirect()->route('monitoring.kgb.index')->with('success', 'Riwayat KGB berhasil dihapus.');
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