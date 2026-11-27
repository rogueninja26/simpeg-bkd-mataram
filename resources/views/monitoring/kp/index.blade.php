@extends('layouts.app')

@section('title', 'Monitoring Kenaikan Pangkat - SIMPEG BKD')

@section('content')
@php
    $canEdit = in_array(Auth::user()->role->nama_role ?? '', ['SuperAdmin', 'Operator_Kepegawaian']);
@endphp

<div class="flex flex-col gap-space-md pb-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-container-lowest p-space-md rounded-2xl shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
        <div>
            <h2 class="font-headline-sm text-headline-sm text-primary font-bold">Monitoring Kenaikan Pangkat (KP)</h2>
            <p class="font-body-sm text-on-surface-variant">Pemantauan proyeksi kenaikan pangkat 4 tahunan dan arsip SK Pangkat PDF.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('monitoring.kp.index', ['filter' => request('filter') === 'proyeksi' ? null : 'proyeksi']) }}"
               class="px-3.5 py-2.5 rounded-xl font-label-md font-semibold {{ request('filter') === 'proyeksi' ? 'bg-error text-on-error' : 'bg-secondary-container text-on-secondary-container' }}">
                {{ request('filter') === 'proyeksi' ? 'Tampilkan Semua' : 'Filter Proyeksi KP (90 Hari)' }}
            </a>
            @if($canEdit)
                <button type="button" onclick="openModal('modal-tambah-kp')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary-container text-on-primary font-label-lg font-semibold shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    <span>Input SK Pangkat Baru</span>
                </button>
            @endif
        </div>
    </div>

    <div class="bg-surface-container-lowest rounded-2xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low border-b border-outline-variant/30 font-label-sm text-on-surface-variant uppercase">
                        <th class="p-4">Pegawai</th>
                        <th class="p-4">Pangkat / Golongan</th>
                        <th class="p-4">Nomor & Tgl SK</th>
                        <th class="p-4">TMT Pangkat</th>
                        <th class="p-4">Proyeksi KP Berikutnya</th>
                        <th class="p-4 text-center">SK PDF</th>
                        @if($canEdit)<th class="p-4 text-right">Aksi</th>@endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20 text-sm">
                    @forelse($pangkatList as $item)
                        @php
                            $isHighlight = request('highlight') == $item->id;
                            $sisaHari = $item->proyeksi_kp_berikutnya ? \Carbon\Carbon::today()->diffInDays($item->proyeksi_kp_berikutnya, false) : null;
                        @endphp
                        <tr class="{{ $isHighlight ? 'bg-error-container/25' : 'hover:bg-surface-container-high/40' }}">
                            <td class="p-4">
                                <div class="font-semibold text-on-surface">{{ $item->pegawai->nama_lengkap ?? '-' }}</div>
                                <div class="text-xs text-on-surface-variant font-tabular-data">NIP/NI. {{ $item->pegawai->nomor_induk ?? '-' }}</div>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-primary-container/15 text-primary">
                                    {{ $item->golongan ? ($item->golongan->kode_golongan . ' - ' . $item->golongan->nama_pangkat) : 'Golongan #' . $item->golongan_id }}
                                </span>
                            </td>
                            <td class="p-4 font-tabular-data">
                                <div class="font-medium">{{ $item->nomor_sk }}</div>
                                <div class="text-xs text-on-surface-variant">Tgl: {{ optional($item->tgl_sk)->format('d/m/Y') }}</div>
                            </td>
                            <td class="p-4 font-tabular-data">{{ optional($item->tmt_pangkat)->format('d/m/Y') }}</td>
                            <td class="p-4 font-tabular-data">
                                <div class="font-semibold">{{ optional($item->proyeksi_kp_berikutnya)->format('d/m/Y') }}</div>
                                @if($item->is_aktif && $sisaHari !== null && $sisaHari <= 90)
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[11px] font-bold {{ $sisaHari <= 30 ? 'bg-error text-on-error' : 'bg-error-container text-on-error-container' }}">
                                        {{ $sisaHari <= 0 ? 'Jatuh Tempo' : 'H-' . $sisaHari }}
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                @if($item->file_sk_path)
                                    <a href="{{ asset('storage/' . $item->file_sk_path) }}" target="_blank"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-secondary-container text-on-secondary-container text-xs font-semibold">
                                        <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span> Lihat
                                    </a>
                                @else
                                    <span class="text-xs text-outline">-</span>
                                @endif
                            </td>
                            @if($canEdit)
                                <td class="p-4 text-right">
                                    <form action="{{ route('monitoring.kp.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus riwayat pangkat ini?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-error hover:bg-error-container">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-8 text-center text-on-surface-variant">Belum ada data riwayat kepangkatan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-outline-variant/20">{{ $pangkatList->withQueryString()->links() }}</div>
    </div>
</div>

@if($canEdit)
<div id="modal-tambah-kp" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30 mb-4">
            <h3 class="font-headline-sm text-lg font-bold text-primary">Input Riwayat Kenaikan Pangkat</h3>
            <button type="button" onclick="closeModal('modal-tambah-kp')" class="p-1 rounded-lg hover:bg-surface-container-high">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="{{ route('monitoring.kp.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            @csrf
            <div class="md:col-span-2">
                <label class="block font-label-md font-semibold mb-1">Pegawai *</label>
                <select name="pegawai_id" required class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface">
                    <option value="">-- Pilih Pegawai --</option>
                    @foreach($pegawaiList as $p)
                        <option value="{{ $p->id }}">{{ $p->nama_lengkap }} ({{ $p->nomor_induk ?? '-' }})</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block font-label-md font-semibold mb-1">Pangkat / Golongan Ruang *</label>
                <select name="golongan_id" required class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface">
                    <option value="">-- Pilih Golongan --</option>
                    @foreach($golonganList as $g)
                        <option value="{{ $g->id }}">{{ $g->kode_golongan }} - {{ $g->nama_pangkat }} ({{ $g->kategori_asn }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-label-md font-semibold mb-1">Nomor SK Pangkat *</label>
                <input type="text" name="nomor_sk" required class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface">
            </div>
            <div>
                <label class="block font-label-md font-semibold mb-1">Tanggal SK *</label>
                <input type="date" name="tgl_sk" required class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface">
            </div>
            <div>
                <label class="block font-label-md font-semibold mb-1">TMT Pangkat *</label>
                <input type="date" name="tmt_pangkat" required class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface">
            </div>
            <div>
                <label class="block font-label-md font-semibold mb-1">Proyeksi KP Berikutnya (Kosongkan = +4 Thn)</label>
                <input type="date" name="proyeksi_kp_berikutnya" class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface">
            </div>
            <div class="md:col-span-2">
                <label class="block font-label-md font-semibold mb-1">Unggah SK Pangkat (PDF, Maks 5MB)</label>
                <input type="file" name="file_sk" accept="application/pdf" class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface">
            </div>
            <div class="md:col-span-2 flex justify-end gap-2 pt-3 border-t border-outline-variant/30">
                <button type="button" onclick="closeModal('modal-tambah-kp')" class="px-4 py-2 rounded-xl border border-outline-variant">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-primary-container text-on-primary font-semibold">Simpan Pangkat</button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
    function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
    function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
</script>
@endpush
@endsection