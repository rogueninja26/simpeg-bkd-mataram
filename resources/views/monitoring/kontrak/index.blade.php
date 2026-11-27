@extends('layouts.app')

@section('title', 'Monitoring Kontrak Kerja - SIMPEG BKD')

@section('content')
@php
    $canEdit = in_array(Auth::user()->role->nama_role ?? '', ['SuperAdmin', 'Operator_Kepegawaian']);
@endphp

<div class="flex flex-col gap-space-md pb-8">
    <!-- Header & Tombol Tambah -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface-container-lowest p-space-md rounded-2xl shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
        <div>
            <h2 class="font-headline-sm text-headline-sm text-primary font-bold">Monitoring Kontrak Kerja (PPPK & SPK)</h2>
            <p class="font-body-sm text-on-surface-variant">Pemantauan masa berlaku kontrak, evaluasi perpanjangan, dan arsip SK/SPK PDF.</p>
        </div>
        @if($canEdit)
            <button type="button" onclick="openModal('modal-tambah-kontrak')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary-container text-on-primary font-label-lg font-semibold shadow-sm hover:opacity-95 transition">
                <span class="material-symbols-outlined text-[20px]">add</span>
                <span>Tambah / Perpanjang Kontrak</span>
            </button>
        @endif
    </div>

    <!-- Filter -->
    <form method="GET" action="{{ route('monitoring.kontrak.index') }}" class="flex flex-wrap items-center gap-3 bg-surface-container-lowest p-4 rounded-2xl shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
        <select name="jenis" class="px-3 py-2 rounded-xl border border-outline-variant bg-surface text-sm">
            <option value="">Semua Jenis Kontrak</option>
            @foreach(['PPPK_PENUH', 'PPPK_PARUH', 'SPK_KONTRAK'] as $j)
                <option value="{{ $j }}" {{ request('jenis') === $j ? 'selected' : '' }}>{{ str_replace('_', ' ', $j) }}</option>
            @endforeach
        </select>
        <select name="status" class="px-3 py-2 rounded-xl border border-outline-variant bg-surface text-sm">
            <option value="">Semua Status Evaluasi</option>
            @foreach(['Berjalan', 'Direkomendasikan_Perpanjang', 'Tidak_Diperpanjang', 'Alih_Status'] as $st)
                <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ str_replace('_', ' ', $st) }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 rounded-xl bg-secondary-container text-on-secondary-container font-label-md font-semibold">Filter</button>
        <a href="{{ route('monitoring.kontrak.index') }}" class="px-3 py-2 text-xs text-on-surface-variant hover:underline">Reset</a>
    </form>

    <!-- Tabel Data -->
    <div class="bg-surface-container-lowest rounded-2xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low border-b border-outline-variant/30 font-label-sm text-on-surface-variant uppercase">
                        <th class="p-4">Pegawai</th>
                        <th class="p-4">Jenis & Golongan</th>
                        <th class="p-4">Nomor & Tgl SK/SPK</th>
                        <th class="p-4">Masa Kontrak</th>
                        <th class="p-4">Honor / Gaji</th>
                        <th class="p-4">Status Evaluasi</th>
                        <th class="p-4 text-center">SK PDF</th>
                        @if($canEdit)<th class="p-4 text-right">Aksi</th>@endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/20 text-sm">
                    @forelse($kontrakList as $item)
                        @php
                            $isHighlight = request('highlight') == $item->id;
                            $sisaHari = $item->tgl_akhir_kontrak ? \Carbon\Carbon::today()->diffInDays($item->tgl_akhir_kontrak, false) : null;
                        @endphp
                        <tr class="{{ $isHighlight ? 'bg-error-container/25' : 'hover:bg-surface-container-high/40' }}">
                            <td class="p-4">
                                <div class="font-semibold text-on-surface">{{ $item->pegawai->nama_lengkap ?? '-' }}</div>
                                <div class="text-xs text-on-surface-variant font-tabular-data">NIP/NI. {{ $item->pegawai->nomor_induk ?? '-' }}</div>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-container/15 text-primary">
                                    {{ str_replace('_', ' ', $item->jenis_kontrak) }}
                                </span>
                                @if($item->golonganPppk)
                                    <div class="text-xs text-on-surface-variant mt-1">
                                        Gol. {{ $item->golonganPppk->kode_golongan }} ({{ $item->golonganPppk->nama_pangkat }})
                                    </div>
                                @endif
                            </td>
                            <td class="p-4 font-tabular-data">
                                <div class="font-medium">{{ $item->nomor_sk_spk }}</div>
                                <div class="text-xs text-on-surface-variant">Tgl: {{ optional($item->tgl_penetapan)->format('d/m/Y') }}</div>
                            </td>
                            <td class="p-4 font-tabular-data">
                                <div>{{ optional($item->tgl_mulai_kontrak)->format('d/m/Y') }} - {{ optional($item->tgl_akhir_kontrak)->format('d/m/Y') }}</div>
                                @if($item->is_aktif && $sisaHari !== null && $sisaHari <= 90)
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[11px] font-bold {{ $sisaHari <= 30 ? 'bg-error text-on-error' : 'bg-error-container text-on-error-container' }}">
                                        {{ $sisaHari <= 0 ? 'Berakhir / Lewat' : 'Sisa ' . $sisaHari . ' hari' }}
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 font-tabular-data">Rp {{ number_format($item->gaji_atau_honor_pokok, 0, ',', '.') }}</td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $item->status_evaluasi === 'Berjalan' ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-high text-on-surface' }}">
                                    {{ str_replace('_', ' ', $item->status_evaluasi) }}
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                @if($item->file_kontrak_path)
                                    <a href="{{ asset('storage/' . $item->file_kontrak_path) }}" target="_blank"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-secondary-container text-on-secondary-container text-xs font-semibold hover:opacity-90">
                                        <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span> Lihat
                                    </a>
                                @else
                                    <span class="text-xs text-outline">-</span>
                                @endif
                            </td>
                            @if($canEdit)
                                <td class="p-4 text-right">
                                    <form action="{{ route('monitoring.kontrak.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus riwayat kontrak ini?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-error hover:bg-error-container" title="Hapus">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="8" class="p-8 text-center text-on-surface-variant">Belum ada data riwayat kontrak kerja.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-outline-variant/20">{{ $kontrakList->withQueryString()->links() }}</div>
    </div>
</div>

@if($canEdit)
<!-- Modal Tambah Kontrak -->
<div id="modal-tambah-kontrak" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30 mb-4">
            <h3 class="font-headline-sm text-lg font-bold text-primary">Input / Perpanjangan Kontrak Kerja</h3>
            <button type="button" onclick="closeModal('modal-tambah-kontrak')" class="p-1 rounded-lg hover:bg-surface-container-high">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="{{ route('monitoring.kontrak.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
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
            <div>
                <label class="block font-label-md font-semibold mb-1">Jenis Kontrak *</label>
                <select name="jenis_kontrak" required class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface">
                    <option value="PPPK_PENUH">PPPK Penuh Waktu</option>
                    <option value="PPPK_PARUH">PPPK Paruh Waktu</option>
                    <option value="SPK_KONTRAK">SPK Kontrak</option>
                </select>
            </div>
            <div>
                <label class="block font-label-md font-semibold mb-1">Golongan PPPK (Opsional)</label>
                <select name="golongan_pppk_id" class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface">
                    <option value="">-- Tidak Ada / Non-PPPK --</option>
                    @foreach($golonganList as $g)
                        <option value="{{ $g->id }}">{{ $g->kode_golongan }} - {{ $g->nama_pangkat }} ({{ $g->kategori_asn }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-label-md font-semibold mb-1">Nomor SK / SPK *</label>
                <input type="text" name="nomor_sk_spk" required class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface">
            </div>
            <div>
                <label class="block font-label-md font-semibold mb-1">Tanggal Penetapan *</label>
                <input type="date" name="tgl_penetapan" required class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface">
            </div>
            <div>
                <label class="block font-label-md font-semibold mb-1">Tgl Mulai Kontrak *</label>
                <input type="date" name="tgl_mulai_kontrak" required class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface">
            </div>
            <div>
                <label class="block font-label-md font-semibold mb-1">Tgl Akhir Kontrak *</label>
                <input type="date" name="tgl_akhir_kontrak" required class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface">
            </div>
            <div>
                <label class="block font-label-md font-semibold mb-1">Gaji / Honor Pokok (Rp) *</label>
                <input type="number" name="gaji_atau_honor_pokok" min="0" required class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface">
            </div>
            <div>
                <label class="block font-label-md font-semibold mb-1">Status Evaluasi *</label>
                <select name="status_evaluasi" required class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface">
                    <option value="Berjalan">Berjalan</option>
                    <option value="Direkomendasikan_Perpanjang">Direkomendasikan Perpanjang</option>
                    <option value="Tidak_Diperpanjang">Tidak Diperpanjang</option>
                    <option value="Alih_Status">Alih Status</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block font-label-md font-semibold mb-1">Unggah File SK/SPK (PDF, Maks 5MB)</label>
                <input type="file" name="file_sk" accept="application/pdf" class="w-full px-3 py-2 rounded-xl border border-outline-variant bg-surface">
            </div>
            <div class="md:col-span-2 flex justify-end gap-2 pt-3 border-t border-outline-variant/30">
                <button type="button" onclick="closeModal('modal-tambah-kontrak')" class="px-4 py-2 rounded-xl border border-outline-variant">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-primary-container text-on-primary font-semibold">Simpan Kontrak</button>
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