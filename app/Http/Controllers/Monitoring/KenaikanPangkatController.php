<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\Models\RiwayatPangkat;
use App\Models\Pegawai;
use App\Models\MasterGolongan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KenaikanPangkatController extends Controller
{
    public function index(Request $request)
    {
        $query = RiwayatPangkat::with(['pegawai', 'golongan']);

        if ((Auth::user()->role->nama_role ?? '') === 'Pegawai') {
            $query->where('pegawai_id', Auth::user()->pegawai->id ?? 0);
        }

        if ($request->get('filter') === 'proyeksi') {
            $query->mendekatiProyeksi(90);
        }

        $pangkatList  = $query->orderBy('proyeksi_kp_berikutnya', 'asc')->paginate(15);
        $pegawaiList = Pegawai::orderBy('nama_lengkap')->get(['id', 'nomor_induk', 'nama_lengkap']);
        $golonganList = MasterGolongan::orderBy('id')->get();

        return view('monitoring.kp.index', compact('pangkatList', 'pegawaiList', 'golonganList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pegawai_id'             => 'required|exists:pegawai,id',
            'golongan_id'            => 'required|exists:master_golongan,id',
            'nomor_sk'               => 'required|string|max:100',
            'tgl_sk'                 => 'required|date',
            'tmt_pangkat'            => 'required|date',
            'proyeksi_kp_berikutnya' => 'nullable|date|after:tmt_pangkat',
            'file_sk'                => 'nullable|file|mimes:pdf|max:5120',
        ]);

        if (empty($validated['proyeksi_kp_berikutnya'])) {
            $validated['proyeksi_kp_berikutnya'] = Carbon::parse($validated['tmt_pangkat'])->addYears(4)->format('Y-m-d');
        }

        DB::transaction(function () use ($request, $validated) {
            if ($request->hasFile('file_sk')) {
                $validated['file_sk_path'] = $request->file('file_sk')->store('efile/sk_pangkat', 'public');
            }

            RiwayatPangkat::where('pegawai_id', $validated['pegawai_id'])->update(['is_aktif' => false]);
            $validated['is_aktif'] = true;

            $record = RiwayatPangkat::create($validated);

            $this->logAudit('INSERT', 'riwayat_pangkat', $record->id, null, $record->toArray());
        });

        return redirect()->route('monitoring.kp.index')->with('success', 'Riwayat Kenaikan Pangkat berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $record = RiwayatPangkat::findOrFail($id);
        $oldData = $record->toArray();

        $validated = $request->validate([
            'golongan_id'            => 'required|exists:master_golongan,id',
            'nomor_sk'               => 'required|string|max:100',
            'tgl_sk'                 => 'required|date',
            'tmt_pangkat'            => 'required|date',
            'proyeksi_kp_berikutnya' => 'required|date|after:tmt_pangkat',
            'file_sk'                => 'nullable|file|mimes:pdf|max:5120',
        ]);

        DB::transaction(function () use ($request, $record, $validated, $oldData) {
            if ($request->hasFile('file_sk')) {
                if ($record->file_sk_path) {
                    Storage::disk('public')->delete($record->file_sk_path);
                }
                $validated['file_sk_path'] = $request->file('file_sk')->store('efile/sk_pangkat', 'public');
            }

            $record->update($validated);

            $this->logAudit('UPDATE', 'riwayat_pangkat', $record->id, $oldData, $record->fresh()->toArray());
        });

        return redirect()->route('monitoring.kp.index')->with('success', 'Riwayat Kenaikan Pangkat berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $record = RiwayatPangkat::findOrFail($id);
        $oldData = $record->toArray();

        DB::transaction(function () use ($record, $oldData) {
            if ($record->file_sk_path) {
                Storage::disk('public')->delete($record->file_sk_path);
            }
            $record->delete();

            if ($oldData['is_aktif']) {
                $latest = RiwayatPangkat::where('pegawai_id', $oldData['pegawai_id'])
                    ->orderBy('tmt_pangkat', 'desc')
                    ->first();
                if ($latest) {
                    $latest->update(['is_aktif' => true]);
                }
            }

            $this->logAudit('DELETE', 'riwayat_pangkat', $oldData['id'], $oldData, null);
        });

        return redirect()->route('monitoring.kp.index')->with('success', 'Riwayat Kenaikan Pangkat berhasil dihapus.');
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