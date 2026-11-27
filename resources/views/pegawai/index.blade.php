@extends('layouts.app')

@section('title', 'Data Induk Pegawai & e-File - SIMPEG BKD Kota Mataram')

@section('content')
<div class="flex flex-col w-full pb-space-2xl space-y-space-lg">
    <!-- Top Action & Context Banner -->
    <div class="w-full bg-surface-container-lowest rounded-xl p-space-lg shadow-sm flex flex-col lg:flex-row items-start lg:items-center justify-between gap-space-md">
        <div class="space-y-1">
            <div class="flex items-center gap-space-sm">
                <span class="px-2 py-0.5 rounded-full bg-primary-container text-on-primary-container font-label-sm text-[11px] uppercase tracking-wider font-semibold">Modul Master Record</span>
                <span class="flex items-center gap-1 font-label-sm text-secondary font-semibold text-[11px]">
                    <span class="w-2 h-2 rounded-full bg-secondary"></span>
                    Data Real-time PostgreSQL
                </span>
            </div>
            <h2 class="font-headline-lg text-headline-lg text-primary tracking-tight">Data Induk Pegawai &amp; e-File Adaptif Multi-Status</h2>
            <p class="font-body-md text-body-md text-on-surface-variant max-w-4xl">
                Master record aparatur BKD Kota Mataram dengan skema atribut data dinamis sesuai regulasi status kepegawaian (PNS, PPPK Penuh Waktu, PPPK Paruh Waktu, dan Tenaga Kontrak Daerah).
            </p>
        </div>

        @if(in_array(Auth::user()->role->nama_role ?? '', ['SuperAdmin', 'Operator_Kepegawaian']))
        <div class="flex flex-wrap items-center gap-space-sm w-full lg:w-auto">
            <button class="flex items-center gap-space-xs px-space-md py-2.5 rounded-xl bg-surface-container-high hover:bg-surface-container-highest text-on-surface font-label-lg text-label-lg transition-colors shadow-sm" type="button">
                <span class="material-symbols-outlined text-[18px]">file_download</span>
                <span>Ekspor Rekap Master</span>
            </button>
            <button onclick="openModal('modalTambahPegawai')" class="flex items-center gap-space-xs px-space-md py-2.5 rounded-xl bg-primary text-on-primary hover:bg-primary-container font-label-lg text-label-lg transition-all shadow-sm" type="button">
                <span class="material-symbols-outlined text-[18px]">person_add</span>
                <span>Tambah Pegawai Baru</span>
            </button>
        </div>
        @endif
    </div>

    <!-- Operational Filter & Multi-Status Tabs -->
    <div class="w-full bg-surface-container-lowest rounded-xl p-space-md shadow-sm space-y-space-md">
        <div class="flex items-center gap-space-xs overflow-x-auto pb-1 scrollbar-none">
            <button class="status-tab-btn flex items-center gap-2 px-4 py-2 rounded-xl bg-primary-container text-on-primary font-label-lg text-label-lg transition-all shrink-0" onclick="filterStatus('all', this)" type="button">
                <span>Semua Status</span>
                <span class="px-2 py-0.5 rounded-full bg-surface-container-lowest/20 font-label-sm text-xs">{{ $statistik['total'] ?? 0 }}</span>
            </button>
            <button class="status-tab-btn flex items-center gap-2 px-4 py-2 rounded-xl bg-surface-container text-on-surface-variant hover:text-on-surface font-label-lg text-label-lg transition-all shrink-0" onclick="filterStatus('pns', this)" type="button">
                <span>PNS / CPNS</span>
                <span class="px-2 py-0.5 rounded-full bg-surface-container-high font-label-sm text-xs">{{ $statistik['pns'] ?? 0 }}</span>
            </button>
            <button class="status-tab-btn flex items-center gap-2 px-4 py-2 rounded-xl bg-surface-container text-on-surface-variant hover:text-on-surface font-label-lg text-label-lg transition-all shrink-0" onclick="filterStatus('pppk-penuh', this)" type="button">
                <span>PPPK Penuh Waktu</span>
                <span class="px-2 py-0.5 rounded-full bg-surface-container-high font-label-sm text-xs">{{ $statistik['pppk_penuh'] ?? 0 }}</span>
            </button>
            <button class="status-tab-btn flex items-center gap-2 px-4 py-2 rounded-xl bg-surface-container text-on-surface-variant hover:text-on-surface font-label-lg text-label-lg transition-all shrink-0" onclick="filterStatus('pppk-paruh', this)" type="button">
                <span>PPPK Paruh Waktu</span>
                <span class="px-2 py-0.5 rounded-full bg-surface-container-high font-label-sm text-xs">{{ $statistik['pppk_paruh'] ?? 0 }}</span>
            </button>
            <button class="status-tab-btn flex items-center gap-2 px-4 py-2 rounded-xl bg-surface-container text-on-surface-variant hover:text-on-surface font-label-lg text-label-lg transition-all shrink-0" onclick="filterStatus('kontrak', this)" type="button">
                <span>Tenaga Kontrak / PJLP</span>
                <span class="px-2 py-0.5 rounded-full bg-surface-container-high font-label-sm text-xs">{{ $statistik['kontrak'] ?? 0 }}</span>
            </button>
        </div>

        <!-- Search & Filter Controls -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-space-sm pt-space-xs">
            <div class="md:col-span-6 relative">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-on-surface-variant text-[20px]">search</span>
                <input class="w-full pl-10 pr-4 py-2 bg-surface rounded-xl text-on-surface font-body-md text-body-md placeholder-on-surface-variant/60 focus:bg-surface-container-lowest focus:outline-none transition-all" id="pegawaiSearch" onkeyup="searchPegawai()" placeholder="Cari NIP, NIK, NI PPPK, atau Nama Pegawai..." type="text"/>
            </div>
            <div class="md:col-span-3">
                <select class="w-full px-3 py-2 bg-surface rounded-xl text-on-surface font-label-md text-label-md focus:bg-surface-container-lowest focus:outline-none transition-all">
                    <option value="">Semua Bidang / Subbag BKD</option>
                    @foreach($unitKerjaList ?? [] as $uk)
                        <option value="{{ $uk->id }}">{{ $uk->nama_unit }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-3 flex items-center gap-space-xs">
                <select class="w-full px-3 py-2 bg-surface rounded-xl text-on-surface font-label-md text-label-md focus:bg-surface-container-lowest focus:outline-none transition-all">
                    <option value="">Status Kelengkapan e-File</option>
                    <option value="lengkap">100% Lengkap (Terverifikasi)</option>
                    <option value="pending">Menunggu Verifikasi (Pending)</option>
                    <option value="kurang">Berkas Kurang (Missing)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Master Split View: Directory Roster (Left) & Active Dossier Profil e-File (Right) -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-space-lg items-start">
        <!-- LEFT: Dense Personnel List -->
        <div class="xl:col-span-4 bg-surface-container-lowest rounded-xl p-space-md shadow-sm space-y-space-sm">
            <div class="flex items-center justify-between pb-space-xs">
                <div class="flex items-center gap-space-xs">
                    <span class="font-label-lg text-label-lg text-primary font-bold">Daftar Pegawai</span>
                    <span class="px-2 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-[11px] font-bold">
                        {{ isset($pegawaiList) ? $pegawaiList->count() : 0 }} Record
                    </span>
                </div>
                <span class="font-body-sm text-[11px] text-on-surface-variant">Klik kartu untuk pratinjau</span>
            </div>

            <div class="space-y-space-xs max-h-[820px] overflow-y-auto pr-1" id="daftarPegawaiContainer">
                @forelse($pegawaiList as $index => $peg)
                    @php
                        $namaTampil = trim(($peg->gelar_depan ? $peg->gelar_depan . ' ' : '') . $peg->nama_lengkap . ($peg->gelar_belakang ? ', ' . $peg->gelar_belakang : ''));
                        $inisial = strtoupper(substr($peg->nama_lengkap, 0, 2));
                        $jumlahDok = $dokumenCounts[$peg->id] ?? 0;
                    @endphp
                    <div class="personnel-card p-space-sm rounded-xl {{ $index === 0 ? 'active-card bg-surface-container-high/80 shadow-sm' : 'bg-surface hover:bg-surface-container-low' }} text-on-surface cursor-pointer transition-all flex items-start gap-space-sm"
                         onclick="selectPersonnelFromDb(this)"
                         data-id="{{ $peg->id }}"
                         data-nama="{{ $namaTampil }}"
                         data-nama-raw="{{ $peg->nama_lengkap }}"
                         data-gelar-depan="{{ $peg->gelar_depan ?? '' }}"
                         data-gelar-belakang="{{ $peg->gelar_belakang ?? '' }}"
                         data-gender="{{ $peg->gender_teks ?? 'L' }}"
                         data-status="{{ $peg->status_teks }}"
                         data-nomor="{{ $peg->nomor_induk ?? '' }}"
                         data-unit="{{ $peg->nama_unit ?? 'Belum Ditempatkan' }}"
                         data-unit-id="{{ $peg->unit_kerja_aktif_id ?? '' }}"
                         data-jabatan="{{ $peg->nama_jabatan ?? '-' }}"
                         data-jabatan-id="{{ $peg->jabatan_aktif_id ?? '' }}"
                         data-pola="{{ $peg->nama_pola ?? 'Reguler' }}"
                         data-pola-id="{{ $peg->pola_kerja_id ?? '' }}"
                         data-nik="{{ $peg->nik ?? '-' }}"
                         data-npwp="{{ $peg->npwp ?? '-' }}"
                         data-tempat-lahir="{{ $peg->tempat_lahir ?? '' }}"
                         data-tgl-lahir="{{ $peg->tgl_lahir ?? '' }}"
                         data-tmt-masuk="{{ $peg->tmt_awal_masuk ?? '' }}"
                         data-ttl="{{ ($peg->tempat_lahir ?? '-') . ', ' . ($peg->tgl_lahir ?? '-') }}"
                         data-hp="{{ $peg->no_hp ?? '-' }}"
                         data-email="{{ $peg->email ?? '-' }}"
                         data-alamat="{{ $peg->alamat_domisili ?? '-' }}"
                         data-kelas="{{ $peg->kelas_jabatan ?? '-' }}"
                         data-tpp="{{ number_format($peg->pagu_tpp_dasar ?? 0, 0, ',', '.') }}">
                        <div class="w-12 h-12 rounded-xl bg-primary-container text-on-primary flex items-center justify-center font-headline-sm shrink-0">
                            {{ $inisial }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-1">
                                <h4 class="font-headline-sm text-[14px] text-primary font-bold truncate leading-tight">{{ $namaTampil }}</h4>
                                <span class="px-1.5 py-0.5 rounded bg-secondary-container text-on-secondary-container font-label-sm text-[10px] font-bold uppercase shrink-0">
                                    {{ $peg->status_teks }}
                                </span>
                            </div>
                            <div class="font-tabular-data text-[11px] text-on-surface-variant mt-0.5">
                                ID: {{ $peg->nomor_induk ?? $peg->nik }}
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-on-surface-variant mt-1.5">
                                <span class="truncate">{{ $peg->nama_unit ?? '-' }}</span>
                                <span class="font-label-sm text-secondary font-bold">{{ $jumlahDok }} Dok</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-space-md text-center text-on-surface-variant font-body-sm">
                        Data pegawai aktif belum ditemukan di database.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- RIGHT: Active Detail Dossier Profile & e-File -->
        @php
            $firstPeg = $pegawaiList->first();
            $firstNama = $firstPeg ? trim(($firstPeg->gelar_depan ? $firstPeg->gelar_depan . ' ' : '') . $firstPeg->nama_lengkap . ($firstPeg->gelar_belakang ? ', ' . $firstPeg->gelar_belakang : '')) : 'Belum Ada Data Pegawai';
        @endphp
        <div class="xl:col-span-8 space-y-space-md">
            <!-- Card 1: Profil Induk -->
            <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm space-y-space-md">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-space-md pb-space-sm">
                    <div class="flex items-center gap-space-md">
                        <div class="w-16 h-16 rounded-xl bg-primary-container text-on-primary flex items-center justify-center font-headline-md shadow-sm shrink-0">
                            <span class="material-symbols-outlined text-[32px]">person</span>
                        </div>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-space-xs">
                                <h3 class="font-headline-lg text-headline-lg text-primary leading-tight" id="detailNama">{{ $firstNama }}</h3>
                                <span class="px-2.5 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-label-sm font-bold uppercase" id="detailStatus">
                                    {{ $firstPeg->status_teks ?? '-' }}
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 font-body-md text-on-surface-variant text-[13px]">
                                <span class="font-tabular-data flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px] text-outline">badge</span>
                                    <span id="detailNomor">ID: {{ $firstPeg->nomor_induk ?? ($firstPeg->nik ?? '-') }}</span>
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px] text-outline">corporate_fare</span>
                                    <span id="detailUnit">{{ $firstPeg->nama_unit ?? '-' }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    @if(in_array(Auth::user()->role->nama_role ?? '', ['SuperAdmin', 'Operator_Kepegawaian']) && $firstPeg)
                    <div class="flex items-center gap-space-xs shrink-0 self-end md:self-center">
                        <button onclick="openModalEdit()" class="px-3.5 py-2 rounded-xl bg-secondary hover:bg-secondary/90 text-on-secondary font-label-md text-label-md flex items-center gap-1 shadow-sm transition-all" type="button">
                            <span class="material-symbols-outlined text-[18px]">edit_document</span>
                            <span>Edit Profil</span>
                        </button>
                    </div>
                    @endif
                </div>

                <!-- Identitas Dasar -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-space-sm p-space-md bg-surface-container-low rounded-xl">
                    <div class="space-y-0.5">
                        <span class="font-label-sm text-[11px] text-on-surface-variant uppercase tracking-wider">NIK Kependudukan</span>
                        <div class="font-tabular-data text-body-md text-on-surface font-semibold" id="detailNik">{{ $firstPeg->nik ?? '-' }}</div>
                    </div>
                    <div class="space-y-0.5">
                        <span class="font-label-sm text-[11px] text-on-surface-variant uppercase tracking-wider">NPWP Pajak</span>
                        <div class="font-tabular-data text-body-md text-on-surface font-semibold" id="detailNpwp">{{ $firstPeg->npwp ?? '-' }}</div>
                    </div>
                    <div class="space-y-0.5">
                        <span class="font-label-sm text-[11px] text-on-surface-variant uppercase tracking-wider">Tempat / Tgl Lahir</span>
                        <div class="text-body-md text-on-surface font-semibold" id="detailTtl">{{ ($firstPeg->tempat_lahir ?? '-') . ', ' . ($firstPeg->tgl_lahir ?? '-') }}</div>
                    </div>
                    <div class="space-y-0.5">
                        <span class="font-label-sm text-[11px] text-on-surface-variant uppercase tracking-wider">Kontak Ponsel</span>
                        <div class="font-tabular-data text-body-md text-on-surface font-semibold" id="detailHp">{{ $firstPeg->no_hp ?? '-' }}</div>
                    </div>
                    <div class="space-y-0.5 md:col-span-2">
                        <span class="font-label-sm text-[11px] text-on-surface-variant uppercase tracking-wider">Alamat Domisili</span>
                        <div class="text-body-md text-on-surface truncate" id="detailAlamat">{{ $firstPeg->alamat_domisili ?? '-' }}</div>
                    </div>
                    <div class="space-y-0.5 md:col-span-2">
                        <span class="font-label-sm text-[11px] text-on-surface-variant uppercase tracking-wider">Email Kedinasan</span>
                        <div class="text-body-md text-on-surface truncate font-semibold" id="detailEmail">{{ $firstPeg->email ?? '-' }}</div>
                    </div>
                </div>

                <!-- Atribut Jabatan & Pola Kerja -->
                <div class="p-space-md rounded-xl bg-surface space-y-space-md">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-space-md">
                        <div class="bg-surface-container-lowest p-space-sm rounded-lg shadow-sm space-y-1">
                            <span class="font-label-sm text-[11px] text-on-surface-variant uppercase">Jabatan Aktif</span>
                            <div class="font-headline-sm text-[15px] text-primary font-bold" id="detailJabatan">{{ $firstPeg->nama_jabatan ?? '-' }}</div>
                        </div>
                        <div class="bg-surface-container-lowest p-space-sm rounded-lg shadow-sm space-y-1">
                            <span class="font-label-sm text-[11px] text-on-surface-variant uppercase">Pola Waktu Kerja</span>
                            <div class="font-headline-sm text-[15px] text-secondary font-bold" id="detailPola">{{ $firstPeg->nama_pola ?? 'Reguler' }}</div>
                        </div>
                        <div class="bg-surface-container-lowest p-space-sm rounded-lg shadow-sm space-y-1">
                            <span class="font-label-sm text-[11px] text-on-surface-variant uppercase">Kelas Jabatan</span>
                            <div class="font-headline-sm text-[15px] text-primary font-bold" id="detailKelas">Kelas {{ $firstPeg->kelas_jabatan ?? '-' }}</div>
                        </div>
                        <div class="bg-surface-container-lowest p-space-sm rounded-lg shadow-sm space-y-1">
                            <span class="font-label-sm text-[11px] text-on-surface-variant uppercase">Pagu Dasar TPP</span>
                            <div class="font-headline-sm text-[15px] text-primary font-bold font-tabular-data" id="detailTpp">Rp {{ number_format($firstPeg->pagu_tpp_dasar ?? 0, 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Manajemen Dokumen Digital (e-File) -->
            @if($firstPeg)
            <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm space-y-space-md">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-space-sm pb-space-xs border-b border-outline-variant/30">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-[20px]">folder_shared</span>
                            <h4 class="font-headline-sm text-base font-bold text-primary">Arsip Dokumen Digital (e-File)</h4>
                            <span id="efileCountBadge" class="px-2 py-0.5 rounded-full bg-primary-container text-on-primary-container font-label-sm text-[11px] font-bold">
                                {{ count($dokumenByPegawai[$firstPeg->id] ?? []) }} Berkas
                            </span>
                        </div>
                        <p class="font-body-sm text-xs text-on-surface-variant mt-0.5">
                            Berkas PDF kepegawaian terverifikasi (SK CPNS/PNS, SK Kontrak PPPK, KTP, NPWP, Ijazah, &amp; Buku Rekening).
                        </p>
                    </div>

                    @if(in_array(Auth::user()->role->nama_role ?? '', ['SuperAdmin', 'Operator_Kepegawaian']))
                    <button onclick="openModalUploadEfile()" type="button" class="px-3.5 py-2 rounded-xl bg-primary hover:bg-primary-container text-on-primary font-label-md text-xs flex items-center gap-1.5 shadow-sm transition-all shrink-0">
                        <span class="material-symbols-outlined text-[18px]">upload_file</span>
                        <span>Unggah PDF e-File</span>
                    </button>
                    @endif
                </div>

                <!-- Container Daftar Dokumen Dinamis -->
                <div id="efileListContainer" class="space-y-2.5">
                    {{-- Diisi otomatis oleh JS saat halaman dimuat dan saat kartu pegawai diklik --}}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Alert Flash Message --}}
@if(session('success'))
    <div id="alert-toast" class="fixed bottom-5 right-5 z-50 bg-secondary text-on-secondary px-5 py-3 rounded-xl shadow-lg font-label-md text-sm flex items-center gap-3">
        <span class="material-symbols-outlined text-[20px]">check_circle</span>
        <span>{{ session('success') }}</span>
        <button type="button" onclick="document.getElementById('alert-toast').remove()" class="opacity-80 hover:opacity-100 text-lg leading-none">&times;</button>
    </div>
@endif

@if($errors->any() || session('error'))
    <div id="alert-error" class="fixed bottom-5 right-5 z-50 bg-error text-on-error px-5 py-3 rounded-xl shadow-lg text-sm max-w-md">
        <div class="flex justify-between items-center font-semibold mb-1">
            <span>Terjadi Kesalahan:</span>
            <button type="button" onclick="document.getElementById('alert-error').remove()" class="opacity-80 hover:opacity-100 text-lg leading-none">&times;</button>
        </div>
        <ul class="list-disc list-inside text-xs space-y-0.5">
            @if(session('error')) <li>{{ session('error') }}</li> @endif
            @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
        </ul>
    </div>
@endif

{{-- MODAL TAMBAH PEGAWAI BARU --}}
<div id="modalTambahPegawai" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-outline-variant/30 flex justify-between items-center sticky top-0 bg-surface-container-lowest z-10">
            <h3 class="font-headline-sm text-lg font-bold text-primary">Tambah Pegawai Baru</h3>
            <button type="button" onclick="closeModal('modalTambahPegawai')" class="text-on-surface-variant hover:text-on-surface text-2xl leading-none">&times;</button>
        </div>
        <form action="{{ route('pegawai.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">NIP / Nomor Induk <span class="text-error">*</span></label>
                    <input type="text" name="nomor_induk" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface focus:border-primary focus:outline-none" placeholder="Contoh: 199508172022031001">
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Status Kepegawaian <span class="text-error">*</span></label>
                    <select name="status_kepegawaian" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface focus:border-primary focus:outline-none">
                        <option value="PNS">PNS</option>
                        <option value="CPNS">CPNS</option>
                        <option value="PPPK_PENUH">PPPK Penuh Waktu</option>
                        <option value="PPPK_PARUH">PPPK Paruh Waktu</option>
                        <option value="KONTRAK">Tenaga Kontrak / PJLP</option>
                    </select>
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Gelar Depan</label>
                    <input type="text" name="gelar_depan" class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface" placeholder="Drs., Ir., Hj.">
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Gelar Belakang</label>
                    <input type="text" name="gelar_belakang" class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface" placeholder="S.Kom., M.M.">
                </div>
                <div class="md:col-span-2">
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Nama Lengkap (Tanpa Gelar) <span class="text-error">*</span></label>
                    <input type="text" name="nama_lengkap" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">NIK Kependudukan <span class="text-error">*</span></label>
                    <input type="text" name="nik" maxlength="16" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface" placeholder="16 digit NIK">
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">NPWP</label>
                    <input type="text" name="npwp" class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Tempat Lahir <span class="text-error">*</span></label>
                    <input type="text" name="tempat_lahir" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface" placeholder="Mataram">
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Tanggal Lahir <span class="text-error">*</span></label>
                    <input type="date" name="tgl_lahir" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">TMT Awal Masuk <span class="text-error">*</span></label>
                    <input type="date" name="tmt_awal_masuk" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Jenis Kelamin <span class="text-error">*</span></label>
                    <select name="jenis_kelamin" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">No. HP / WhatsApp</label>
                    <input type="text" name="no_hp" class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface" placeholder="0812xxxxxxxx">
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Unit Kerja / Bidang <span class="text-error">*</span></label>
                    <select name="unit_kerja_aktif_id" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                        <option value="">-- Pilih Unit Kerja --</option>
                        @foreach($unitKerjaList as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Jabatan <span class="text-error">*</span></label>
                    <select name="jabatan_aktif_id" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach($jabatanList as $jab)
                            <option value="{{ $jab->id }}">{{ $jab->nama_jabatan }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Pola Kerja / Shift <span class="text-error">*</span></label>
                    <select name="pola_kerja_id" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                        <option value="">-- Pilih Pola Kerja --</option>
                        @foreach($polaKerjaList as $pola)
                            <option value="{{ $pola->id }}">{{ $pola->nama_pola }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Email Kedinasan</label>
                    <input type="email" name="email" class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface" placeholder="nama@mataramkota.go.id">
                </div>
                <div class="md:col-span-2">
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Alamat Domisili</label>
                    <textarea name="alamat_domisili" rows="2" class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface"></textarea>
                </div>
            </div>
            <div class="pt-4 border-t border-outline-variant/30 flex justify-end gap-2">
                <button type="button" onclick="closeModal('modalTambahPegawai')" class="px-4 py-2 rounded-xl bg-surface-container-high text-on-surface font-label-md text-sm hover:bg-surface-container-highest">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-primary text-on-primary font-label-md text-sm hover:bg-primary-container">Simpan &amp; Buat Akun</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT PROFIL PEGAWAI --}}
<div id="modalEditPegawai" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-outline-variant/30 flex justify-between items-center sticky top-0 bg-surface-container-lowest z-10">
            <h3 class="font-headline-sm text-lg font-bold text-primary">Edit Profil Pegawai</h3>
            <button type="button" onclick="closeModal('modalEditPegawai')" class="text-on-surface-variant hover:text-on-surface text-2xl leading-none">&times;</button>
        </div>
        <form id="formEditPegawai" action="" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">NIP / Nomor Induk <span class="text-error">*</span></label>
                    <input type="text" id="edit_nomor_induk" name="nomor_induk" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Status Kepegawaian <span class="text-error">*</span></label>
                    <select id="edit_status_kepegawaian" name="status_kepegawaian" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                        <option value="PNS">PNS</option>
                        <option value="CPNS">CPNS</option>
                        <option value="PPPK_PENUH">PPPK Penuh Waktu</option>
                        <option value="PPPK_PARUH">PPPK Paruh Waktu</option>
                        <option value="KONTRAK">Tenaga Kontrak / PJLP</option>
                    </select>
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Gelar Depan</label>
                    <input type="text" id="edit_gelar_depan" name="gelar_depan" class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Gelar Belakang</label>
                    <input type="text" id="edit_gelar_belakang" name="gelar_belakang" class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                </div>
                <div class="md:col-span-2">
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Nama Lengkap <span class="text-error">*</span></label>
                    <input type="text" id="edit_nama_lengkap" name="nama_lengkap" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">NIK Kependudukan <span class="text-error">*</span></label>
                    <input type="text" id="edit_nik" name="nik" maxlength="16" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">NPWP</label>
                    <input type="text" id="edit_npwp" name="npwp" class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Tempat Lahir <span class="text-error">*</span></label>
                    <input type="text" id="edit_tempat_lahir" name="tempat_lahir" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Tanggal Lahir <span class="text-error">*</span></label>
                    <input type="date" id="edit_tgl_lahir" name="tgl_lahir" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">TMT Awal Masuk <span class="text-error">*</span></label>
                    <input type="date" id="edit_tmt_awal_masuk" name="tmt_awal_masuk" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Jenis Kelamin <span class="text-error">*</span></label>
                    <select id="edit_jenis_kelamin" name="jenis_kelamin" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">No. HP / WhatsApp</label>
                    <input type="text" id="edit_no_hp" name="no_hp" class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Unit Kerja / Bidang <span class="text-error">*</span></label>
                    <select id="edit_unit_kerja_aktif_id" name="unit_kerja_aktif_id" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                        @foreach($unitKerjaList as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Jabatan <span class="text-error">*</span></label>
                    <select id="edit_jabatan_aktif_id" name="jabatan_aktif_id" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                        @foreach($jabatanList as $jab)
                            <option value="{{ $jab->id }}">{{ $jab->nama_jabatan }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Pola Kerja / Shift <span class="text-error">*</span></label>
                    <select id="edit_pola_kerja_id" name="pola_kerja_id" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                        @foreach($polaKerjaList as $pola)
                            <option value="{{ $pola->id }}">{{ $pola->nama_pola }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Email Kedinasan</label>
                    <input type="email" id="edit_email" name="email" class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                </div>
                <div class="md:col-span-2">
                    <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Alamat Domisili</label>
                    <textarea id="edit_alamat_domisili" name="alamat_domisili" rows="2" class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface"></textarea>
                </div>
            </div>
            <div class="pt-4 border-t border-outline-variant/30 flex justify-end gap-2">
                <button type="button" onclick="closeModal('modalEditPegawai')" class="px-4 py-2 rounded-xl bg-surface-container-high text-on-surface font-label-md text-sm hover:bg-surface-container-highest">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-secondary text-on-secondary font-label-md text-sm hover:bg-secondary/90">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL UNGGAH DOKUMEN E-FILE --}}
<div id="modalUploadEfile" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-2xl shadow-xl w-full max-w-lg">
        <div class="px-6 py-4 border-b border-outline-variant/30 flex justify-between items-center">
            <div>
                <h3 class="font-headline-sm text-lg font-bold text-primary">Unggah Dokumen e-File (PDF)</h3>
                <p id="uploadEfileTargetNama" class="text-xs text-on-surface-variant mt-0.5"></p>
            </div>
            <button type="button" onclick="closeModal('modalUploadEfile')" class="text-on-surface-variant hover:text-on-surface text-2xl leading-none">&times;</button>
        </div>
        <form id="formUploadEfile" action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Kategori Dokumen <span class="text-error">*</span></label>
                <select name="kategori_dokumen" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface focus:border-primary focus:outline-none">
                    <option value="SK CPNS / Pengangkatan Awal">SK CPNS / Pengangkatan Awal</option>
                    <option value="SK PNS / SK PPPK / SPK Kontrak">SK PNS / SK PPPK / SPK Kontrak</option>
                    <option value="SK Pangkat / Golongan Terakhir">SK Pangkat / Golongan Terakhir</option>
                    <option value="SK Jabatan Terakhir">SK Jabatan Terakhir</option>
                    <option value="Ijazah & Transkrip Terakhir">Ijazah &amp; Transkrip Terakhir</option>
                    <option value="KTP & Kartu Keluarga">KTP &amp; Kartu Keluarga</option>
                    <option value="NPWP & Buku Rekening Gaji">NPWP &amp; Buku Rekening Gaji</option>
                    <option value="Dokumen Pendukung Lainnya">Dokumen Pendukung Lainnya</option>
                </select>
            </div>
            <div>
                <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Berkas PDF (Maks. 5 MB) <span class="text-error">*</span></label>
                <input type="file" name="file_pdf" accept="application/pdf" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-primary file:text-on-primary file:text-xs file:font-semibold">
            </div>
            <div class="pt-4 border-t border-outline-variant/30 flex justify-end gap-2">
                <button type="button" onclick="closeModal('modalUploadEfile')" class="px-4 py-2 rounded-xl bg-surface-container-high text-on-surface font-label-md text-sm">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-primary text-on-primary font-label-md text-sm">Unggah Berkas</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL PRATINJAU PDF E-FILE --}}
<div id="modalPreviewEfile" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-2xl shadow-xl w-full max-w-4xl h-[88vh] flex flex-col overflow-hidden">
        <div class="px-6 py-3.5 border-b border-outline-variant/30 flex justify-between items-center">
            <div class="min-w-0 pr-4">
                <h3 id="previewEfileTitle" class="font-headline-sm text-base font-bold text-primary truncate">Pratinjau Dokumen</h3>
                <p id="previewEfileSubtitle" class="text-xs text-on-surface-variant truncate"></p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a id="previewEfileDownload" href="#" target="_blank" class="px-3 py-1.5 rounded-lg bg-surface-container-high text-on-surface text-xs font-semibold hover:bg-surface-container-highest flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">open_in_new</span>
                    <span>Tab Baru</span>
                </a>
                <button type="button" onclick="closeModal('modalPreviewEfile')" class="text-on-surface-variant hover:text-on-surface text-2xl leading-none px-1">&times;</button>
            </div>
        </div>
        <div class="flex-1 bg-surface-container">
            <iframe id="previewEfileFrame" src="" class="w-full h-full border-0"></iframe>
        </div>
    </div>
</div>

{{-- MODAL VERIFIKASI DOKUMEN E-FILE --}}
<div id="modalVerifikasiEfile" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-2xl shadow-xl w-full max-w-md">
        <div class="px-6 py-4 border-b border-outline-variant/30 flex justify-between items-center">
            <h3 class="font-headline-sm text-lg font-bold text-primary">Verifikasi Berkas e-File</h3>
            <button type="button" onclick="closeModal('modalVerifikasiEfile')" class="text-on-surface-variant hover:text-on-surface text-2xl leading-none">&times;</button>
        </div>
        <form id="formVerifikasiEfile" action="" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PATCH')
            <div>
                <p id="verifikasiDocLabel" class="text-xs font-semibold text-on-surface-variant mb-3"></p>
                <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Keputusan Verifikasi <span class="text-error">*</span></label>
                <select id="verifikasi_status" name="status_verifikasi" required class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface">
                    <option value="Valid">Valid (Sesuai &amp; Terverifikasi)</option>
                    <option value="Ditolak">Ditolak (Perlu Unggah Ulang)</option>
                    <option value="Pending">Pending (Menunggu Pemeriksaan)</option>
                </select>
            </div>
            <div>
                <label class="block font-label-sm text-xs font-semibold text-on-surface mb-1">Catatan Operator Kepegawaian</label>
                <textarea id="verifikasi_catatan" name="catatan_operator" rows="3" class="w-full rounded-xl bg-surface border border-outline-variant px-3 py-2 text-sm text-on-surface" placeholder="Opsional: tulis alasan jika berkas ditolak atau catatan verifikasi..."></textarea>
            </div>
            <div class="pt-4 border-t border-outline-variant/30 flex justify-end gap-2">
                <button type="button" onclick="closeModal('modalVerifikasiEfile')" class="px-4 py-2 rounded-xl bg-surface-container-high text-on-surface font-label-md text-sm">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-secondary text-on-secondary font-label-md text-sm">Simpan Status</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const dokumenByPegawai = @json($dokumenByPegawai ?? []);
    const canVerifyEfile = @json(in_array(Auth::user()->role->nama_role ?? '', ['SuperAdmin', 'Operator_Kepegawaian']));

    function openModal(id) {
        document.getElementById(id)?.classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id)?.classList.add('hidden');
        if (id === 'modalPreviewEfile') {
            document.getElementById('previewEfileFrame').src = '';
        }
    }

    function getActiveCard() {
        return document.querySelector('.personnel-card.active-card') || document.querySelector('.personnel-card');
    }

    function openModalEdit() {
        const activeCard = getActiveCard();
        if (!activeCard) return;

        const d = activeCard.dataset;
        document.getElementById('formEditPegawai').action = `/data-pegawai/${d.id}`;
        document.getElementById('edit_nomor_induk').value = d.nomor || '';
        document.getElementById('edit_status_kepegawaian').value = d.status || 'PNS';
        document.getElementById('edit_gelar_depan').value = d.gelarDepan || '';
        document.getElementById('edit_gelar_belakang').value = d.gelarBelakang || '';
        document.getElementById('edit_nama_lengkap').value = d.namaRaw || '';
        document.getElementById('edit_nik').value = (d.nik && d.nik !== '-') ? d.nik : '';
        document.getElementById('edit_npwp').value = (d.npwp && d.npwp !== '-') ? d.npwp : '';
        document.getElementById('edit_tempat_lahir').value = d.tempatLahir || '';
        document.getElementById('edit_tgl_lahir').value = d.tglLahir || '';
        document.getElementById('edit_tmt_awal_masuk').value = d.tmtMasuk || '';
        document.getElementById('edit_jenis_kelamin').value = d.gender || 'L';
        document.getElementById('edit_no_hp').value = (d.hp && d.hp !== '-') ? d.hp : '';
        document.getElementById('edit_unit_kerja_aktif_id').value = d.unitId || '';
        document.getElementById('edit_jabatan_aktif_id').value = d.jabatanId || '';
        document.getElementById('edit_pola_kerja_id').value = d.polaId || '';
        document.getElementById('edit_email').value = (d.email && d.email !== '-') ? d.email : '';
        document.getElementById('edit_alamat_domisili').value = (d.alamat && d.alamat !== '-') ? d.alamat : '';

        openModal('modalEditPegawai');
    }

    function openModalUploadEfile() {
        const activeCard = getActiveCard();
        if (!activeCard) return;

        const d = activeCard.dataset;
        document.getElementById('formUploadEfile').action = `/data-pegawai/${d.id}/efile`;
        document.getElementById('uploadEfileTargetNama').innerText = `Pegawai: ${d.nama} (${d.nomor})`;
        openModal('modalUploadEfile');
    }

    function openPreviewEfile(title, fileName, fileUrl) {
        document.getElementById('previewEfileTitle').innerText = title;
        document.getElementById('previewEfileSubtitle').innerText = fileName;
        document.getElementById('previewEfileDownload').href = fileUrl;
        document.getElementById('previewEfileFrame').src = fileUrl;
        openModal('modalPreviewEfile');
    }

    function openVerifikasiEfile(docId, kategori, statusSaatIni, catatanSaatIni) {
        document.getElementById('formVerifikasiEfile').action = `/data-pegawai/efile/${docId}/verifikasi`;
        document.getElementById('verifikasiDocLabel').innerText = `Dokumen: ${kategori}`;
        document.getElementById('verifikasi_status').value = statusSaatIni || 'Valid';
        document.getElementById('verifikasi_catatan').value = catatanSaatIni || '';
        openModal('modalVerifikasiEfile');
    }

    function renderEfileList(pegawaiId) {
        const container = document.getElementById('efileListContainer');
        const badge = document.getElementById('efileCountBadge');
        if (!container) return;

        const docs = dokumenByPegawai[pegawaiId] || [];
        if (badge) badge.innerText = `${docs.length} Berkas`;

        if (docs.length === 0) {
            container.innerHTML = `
                <div class="p-6 rounded-xl bg-surface text-center border border-dashed border-outline-variant">
                    <span class="material-symbols-outlined text-on-surface-variant text-[32px]">folder_off</span>
                    <p class="font-body-sm text-xs text-on-surface-variant mt-1">Belum ada berkas PDF e-File yang diunggah untuk pegawai ini.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = docs.map(doc => {
            let statusBadge = '';
            if (doc.status_verifikasi === 'Valid') {
                statusBadge = `<span class="px-2 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-[10px] font-bold uppercase">Valid</span>`;
            } else if (doc.status_verifikasi === 'Ditolak') {
                statusBadge = `<span class="px-2 py-0.5 rounded-full bg-error-container text-on-error-container font-label-sm text-[10px] font-bold uppercase">Ditolak</span>`;
            } else {
                statusBadge = `<span class="px-2 py-0.5 rounded-full bg-tertiary-container text-on-tertiary-container font-label-sm text-[10px] font-bold uppercase">Pending</span>`;
            }

            const catatanHtml = doc.catatan_operator
                ? `<div class="text-[11px] text-error mt-1">Catatan: ${doc.catatan_operator}</div>`
                : '';

            const verifBtn = canVerifyEfile
                ? `<button type="button" onclick='openVerifikasiEfile(${doc.id}, ${JSON.stringify(doc.kategori_dokumen)}, ${JSON.stringify(doc.status_verifikasi)}, ${JSON.stringify(doc.catatan_operator || "")})' class="px-2.5 py-1.5 rounded-lg bg-secondary-container hover:bg-secondary hover:text-on-secondary text-on-secondary-container font-label-sm text-xs font-semibold flex items-center gap-1 transition-all">
                        <span class="material-symbols-outlined text-[15px]">verified</span>
                        <span>Verifikasi</span>
                   </button>`
                : '';

            return `
                <div class="p-3.5 rounded-xl bg-surface hover:bg-surface-container-low transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-lg bg-error-container/50 text-error flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[22px]">picture_as_pdf</span>
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-headline-sm text-sm font-bold text-primary truncate">${doc.kategori_dokumen}</span>
                                ${statusBadge}
                            </div>
                            <div class="text-xs text-on-surface-variant truncate mt-0.5">${doc.nama_file}</div>
                            <div class="text-[11px] text-on-surface-variant/80 mt-0.5">
                                Diunggah: ${doc.uploaded_at ?? '-'} ${doc.verifier_nama && doc.verifier_nama !== '-' ? '• Verifikator: ' + doc.verifier_nama : ''}
                            </div>
                            ${catatanHtml}
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                        <button type="button" onclick='openPreviewEfile(${JSON.stringify(doc.kategori_dokumen)}, ${JSON.stringify(doc.nama_file)}, ${JSON.stringify(doc.file_url)})' class="px-2.5 py-1.5 rounded-lg bg-surface-container-high hover:bg-surface-container-highest text-on-surface font-label-sm text-xs font-semibold flex items-center gap-1 transition-all">
                            <span class="material-symbols-outlined text-[15px]">visibility</span>
                            <span>Pratinjau</span>
                        </button>
                        ${verifBtn}
                    </div>
                </div>
            `;
        }).join('');
    }

    function filterStatus(status, btnElement) {
        document.querySelectorAll('.status-tab-btn').forEach(btn => {
            btn.classList.remove('bg-primary-container', 'text-on-primary');
            btn.classList.add('bg-surface-container', 'text-on-surface-variant');
        });
        btnElement.classList.remove('bg-surface-container', 'text-on-surface-variant');
        btnElement.classList.add('bg-primary-container', 'text-on-primary');

        document.querySelectorAll('.personnel-card').forEach(card => {
            const st = (card.dataset.status || '').toLowerCase();
            if (status === 'all') {
                card.style.display = 'flex';
            } else if (status === 'pns') {
                card.style.display = (st === 'pns' || st === 'cpns') ? 'flex' : 'none';
            } else if (status === 'pppk-penuh') {
                card.style.display = st.includes('penuh') ? 'flex' : 'none';
            } else if (status === 'pppk-paruh') {
                card.style.display = st.includes('paruh') ? 'flex' : 'none';
            } else if (status === 'kontrak') {
                card.style.display = (!st.includes('pns') && !st.includes('pppk')) ? 'flex' : 'none';
            }
        });
    }

    function selectPersonnelFromDb(element) {
        document.querySelectorAll('.personnel-card').forEach(card => {
            card.classList.remove('active-card', 'bg-surface-container-high/80', 'shadow-sm');
            card.classList.add('bg-surface');
        });
        element.classList.remove('bg-surface');
        element.classList.add('active-card', 'bg-surface-container-high/80', 'shadow-sm');

        document.getElementById('detailNama').innerText = element.dataset.nama;
        document.getElementById('detailStatus').innerText = element.dataset.status;
        document.getElementById('detailNomor').innerText = 'ID: ' + element.dataset.nomor;
        document.getElementById('detailUnit').innerText = element.dataset.unit;
        document.getElementById('detailNik').innerText = element.dataset.nik;
        document.getElementById('detailNpwp').innerText = element.dataset.npwp;
        document.getElementById('detailTtl').innerText = element.dataset.ttl;
        document.getElementById('detailHp').innerText = element.dataset.hp;
        document.getElementById('detailAlamat').innerText = element.dataset.alamat;
        document.getElementById('detailEmail').innerText = element.dataset.email;
        document.getElementById('detailJabatan').innerText = element.dataset.jabatan;
        document.getElementById('detailPola').innerText = element.dataset.pola;
        document.getElementById('detailKelas').innerText = 'Kelas ' + element.dataset.kelas;
        document.getElementById('detailTpp').innerText = 'Rp ' + element.dataset.tpp;

        renderEfileList(element.dataset.id);
    }

    function searchPegawai() {
        const keyword = document.getElementById('pegawaiSearch').value.toLowerCase();
        document.querySelectorAll('.personnel-card').forEach(card => {
            const text = card.innerText.toLowerCase();
            card.style.display = text.includes(keyword) ? 'flex' : 'none';
        });
    }

    // Render daftar dokumen pegawai pertama saat halaman pertama kali dibuka
    document.addEventListener('DOMContentLoaded', () => {
        const firstCard = getActiveCard();
        if (firstCard) {
            renderEfileList(firstCard.dataset.id);
        }
    });
</script>
@endpush