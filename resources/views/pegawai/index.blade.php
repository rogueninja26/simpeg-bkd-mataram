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
                    Sinkron BKN &amp; SIASN
                </span>
            </div>
            <h2 class="font-headline-lg text-headline-lg text-primary tracking-tight">Data Induk Pegawai &amp; e-File Adaptif Multi-Status</h2>
            <p class="font-body-md text-body-md text-on-surface-variant max-w-4xl">
                Master record aparatur BKD Kota Mataram dengan skema atribut data dinamis sesuai regulasi status kepegawaian (PNS, PPPK Penuh Waktu, PPPK Paruh Waktu, dan Tenaga Kontrak Daerah).
            </p>
        </div>

        @if(in_array(Auth::user()->role ?? '', ['SuperAdmin', 'Operator_Kepegawaian']))
        <div class="flex flex-wrap items-center gap-space-sm w-full lg:w-auto">
            <button class="flex items-center gap-space-xs px-space-md py-2.5 rounded-xl bg-surface-container-high hover:bg-surface-container-highest text-on-surface font-label-lg text-label-lg transition-colors shadow-sm" type="button">
                <span class="material-symbols-outlined text-[18px]">file_download</span>
                <span>Ekspor Rekap Master</span>
            </button>
            <button class="flex items-center gap-space-xs px-space-md py-2.5 rounded-xl bg-primary text-on-primary hover:bg-primary-container font-label-lg text-label-lg transition-all shadow-sm" onclick="document.getElementById('modalTambahPegawai').classList.remove('hidden')" type="button">
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
                <span class="px-2 py-0.5 rounded-full bg-surface-container-lowest/20 font-label-sm text-xs">{{ $statistik['total'] ?? 184 }}</span>
            </button>
            <button class="status-tab-btn flex items-center gap-2 px-4 py-2 rounded-xl bg-surface-container text-on-surface-variant hover:text-on-surface font-label-lg text-label-lg transition-all shrink-0" onclick="filterStatus('pns', this)" type="button">
                <span>PNS / CPNS</span>
                <span class="px-2 py-0.5 rounded-full bg-surface-container-high font-label-sm text-xs">{{ $statistik['pns'] ?? 76 }}</span>
            </button>
            <button class="status-tab-btn flex items-center gap-2 px-4 py-2 rounded-xl bg-surface-container text-on-surface-variant hover:text-on-surface font-label-lg text-label-lg transition-all shrink-0" onclick="filterStatus('pppk-penuh', this)" type="button">
                <span>PPPK Penuh Waktu</span>
                <span class="px-2 py-0.5 rounded-full bg-surface-container-high font-label-sm text-xs">{{ $statistik['pppk_penuh'] ?? 42 }}</span>
            </button>
            <button class="status-tab-btn flex items-center gap-2 px-4 py-2 rounded-xl bg-surface-container text-on-surface-variant hover:text-on-surface font-label-lg text-label-lg transition-all shrink-0" onclick="filterStatus('pppk-paruh', this)" type="button">
                <span>PPPK Paruh Waktu</span>
                <span class="px-2 py-0.5 rounded-full bg-surface-container-high font-label-sm text-xs">{{ $statistik['pppk_paruh'] ?? 28 }}</span>
            </button>
            <button class="status-tab-btn flex items-center gap-2 px-4 py-2 rounded-xl bg-surface-container text-on-surface-variant hover:text-on-surface font-label-lg text-label-lg transition-all shrink-0" onclick="filterStatus('kontrak', this)" type="button">
                <span>Tenaga Kontrak / PJLP</span>
                <span class="px-2 py-0.5 rounded-full bg-surface-container-high font-label-sm text-xs">{{ $statistik['kontrak'] ?? 38 }}</span>
            </button>
        </div>

        <!-- Search & Filter Controls -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-space-sm pt-space-xs">
            <div class="md:col-span-6 relative">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-on-surface-variant text-[20px]">search</span>
                <input class="w-full pl-10 pr-4 py-2 bg-surface rounded-xl text-on-surface font-body-md text-body-md placeholder-on-surface-variant/60 focus:bg-surface-container-lowest focus:outline-none transition-all" id="pegawaiSearch" placeholder="Cari NIP, NIK, NI PPPK, atau Nama Pegawai..." type="text"/>
            </div>
            <div class="md:col-span-3">
                <select class="w-full px-3 py-2 bg-surface rounded-xl text-on-surface font-label-md text-label-md focus:bg-surface-container-lowest focus:outline-none transition-all">
                    <option value="">Semua Bidang / Subbag BKD</option>
                    <option value="sekretariat">Sekretariat &amp; Subbag Umum</option>
                    <option value="pendataan">Bidang Pendataan &amp; Penetapan</option>
                    <option value="penagihan">Bidang Penagihan &amp; Keberatan</option>
                    <option value="anggaran">Bidang Anggaran &amp; Perbendaharaan</option>
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
                    <span class="px-2 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-[11px] font-bold">{{ $pegawaiList->count() ?? 5 }} Record</span>
                </div>
                <span class="font-body-sm text-[11px] text-on-surface-variant">Klik kartu untuk pratinjau</span>
            </div>

            <div class="space-y-space-xs max-h-[820px] overflow-y-auto pr-1">
                <!-- Contoh Item Statis / Loop Data Seeding -->
                <div class="personnel-card p-space-sm rounded-xl bg-surface-container-high/80 text-on-surface cursor-pointer shadow-sm transition-all flex items-start gap-space-sm" onclick="selectPersonnel(this, 'pppk-paruh')">
                    <div class="w-12 h-12 rounded-xl bg-primary-container text-on-primary flex items-center justify-center font-headline-sm shrink-0">LF</div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-1">
                            <h4 class="font-headline-sm text-[14px] text-primary font-bold truncate leading-tight">Lalu Muhammad Farhan, S.E.</h4>
                            <span class="px-1.5 py-0.5 rounded bg-secondary-container text-on-secondary-container font-label-sm text-[10px] font-bold uppercase shrink-0">Paruh Waktu</span>
                        </div>
                        <div class="font-tabular-data text-[11px] text-on-surface-variant mt-0.5">NI PPPK: 19880412 202421 1 008</div>
                        <div class="flex items-center justify-between text-[11px] text-on-surface-variant mt-1.5">
                            <span class="truncate">Subbag Umum &amp; Kepegawaian</span>
                            <span class="font-label-sm text-secondary font-bold">8/8 Dok</span>
                        </div>
                    </div>
                </div>

                <div class="personnel-card p-space-sm rounded-xl bg-surface hover:bg-surface-container-low text-on-surface cursor-pointer transition-all flex items-start gap-space-sm" onclick="selectPersonnel(this, 'pns')">
                    <div class="w-12 h-12 rounded-xl bg-primary text-on-primary flex items-center justify-center font-headline-sm shrink-0">BN</div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-1">
                            <h4 class="font-headline-sm text-[14px] text-on-surface font-semibold truncate leading-tight">Baiq Nurul Hayati, S.STP, M.Si</h4>
                            <span class="px-1.5 py-0.5 rounded bg-primary-fixed text-on-primary-fixed font-label-sm text-[10px] font-bold uppercase shrink-0">PNS III/d</span>
                        </div>
                        <div class="font-tabular-data text-[11px] text-on-surface-variant mt-0.5">NIP: 19820516 200701 2 009</div>
                        <div class="flex items-center justify-between text-[11px] text-on-surface-variant mt-1.5">
                            <span class="truncate">Analis Keuangan Pusat &amp; Daerah</span>
                            <span class="font-label-sm text-secondary font-bold">12/12 Dok</span>
                        </div>
                    </div>
                </div>

                <div class="personnel-card p-space-sm rounded-xl bg-surface hover:bg-surface-container-low text-on-surface cursor-pointer transition-all flex items-start gap-space-sm" onclick="selectPersonnel(this, 'pppk-penuh')">
                    <div class="w-12 h-12 rounded-xl bg-secondary text-on-secondary flex items-center justify-center font-headline-sm shrink-0">IG</div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-1">
                            <h4 class="font-headline-sm text-[14px] text-on-surface font-semibold truncate leading-tight">I Gusti Bagus Arya, S.Kom</h4>
                            <span class="px-1.5 py-0.5 rounded bg-surface-container-highest text-primary font-label-sm text-[10px] font-bold uppercase shrink-0">PPPK Penuh</span>
                        </div>
                        <div class="font-tabular-data text-[11px] text-on-surface-variant mt-0.5">NI PPPK: 19910908 202221 1 004</div>
                        <div class="flex items-center justify-between text-[11px] text-on-surface-variant mt-1.5">
                            <span class="truncate">Pranata Komputer Ahli Pertama</span>
                            <span class="font-label-sm text-secondary font-bold">9/9 Dok</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Active Detail Dossier Profile & Dynamic Multi-Status Grid -->
        <div class="xl:col-span-8 space-y-space-md">
            <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm space-y-space-md">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-space-md pb-space-sm">
                    <div class="flex items-center gap-space-md">
                        <div class="w-16 h-16 rounded-xl bg-primary-container text-on-primary flex items-center justify-center font-headline-md shadow-sm shrink-0">
                            <span class="material-symbols-outlined text-[32px]">person</span>
                        </div>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-space-xs">
                                <h3 class="font-headline-lg text-headline-lg text-primary leading-tight" id="activeName">Lalu Muhammad Farhan, S.E.</h3>
                                <span class="px-2.5 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-label-sm font-bold uppercase" id="activeBadge">
                                    PPPK Paruh Waktu
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 font-body-md text-on-surface-variant text-[13px]">
                                <span class="font-tabular-data flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px] text-outline">badge</span>
                                    <span id="activeIdentifier">NI PPPK: 19880412 202421 1 008</span>
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px] text-outline">corporate_fare</span>
                                    <span id="activeUnit">Subbagian Umum &amp; Kepegawaian BKD</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-space-xs shrink-0 self-end md:self-center">
                        <button class="px-3.5 py-2 rounded-xl bg-secondary hover:bg-secondary/90 text-on-secondary font-label-md text-label-md flex items-center gap-1 shadow-sm transition-all" type="button">
                            <span class="material-symbols-outlined text-[18px]">edit_document</span>
                            <span>Edit Profil</span>
                        </button>
                    </div>
                </div>

                <!-- Dynamic Container -->
                <div class="p-space-md rounded-xl bg-surface space-y-space-md" id="dynamicStatusBox">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-space-md">
                        <div class="bg-surface-container-lowest p-space-sm rounded-lg shadow-sm space-y-1">
                            <span class="font-label-sm text-[11px] text-on-surface-variant uppercase">Pola Waktu Kerja</span>
                            <div class="font-headline-sm text-headline-sm text-secondary font-bold">4 Jam / Hari</div>
                            <p class="font-body-sm text-[11px] text-on-surface-variant">Shift: 08.00 - 12.00 WITA</p>
                        </div>
                        <div class="bg-surface-container-lowest p-space-sm rounded-lg shadow-sm space-y-1">
                            <span class="font-label-sm text-[11px] text-on-surface-variant uppercase">TMT Kontrak Daerah</span>
                            <div class="font-tabular-data font-headline-sm text-[16px] text-primary font-bold">01 Jan 2024</div>
                            <p class="font-body-sm text-[11px] text-on-surface-variant">Berakhir: 31 Des 2025</p>
                        </div>
                        <div class="bg-surface-container-lowest p-space-sm rounded-lg shadow-sm space-y-1">
                            <span class="font-label-sm text-[11px] text-on-surface-variant uppercase">Kompensasi Bulanan</span>
                            <div class="font-headline-sm text-headline-sm text-primary font-bold">Rp 2.450.000</div>
                            <p class="font-body-sm text-[11px] text-secondary font-semibold">Skema Proporsional Perda</p>
                        </div>
                        <div class="bg-surface-container-lowest p-space-sm rounded-lg shadow-sm space-y-1">
                            <span class="font-label-sm text-[11px] text-on-surface-variant uppercase">Target Output Kinerja</span>
                            <div class="flex items-center gap-2">
                                <span class="font-headline-sm text-[18px] text-primary font-bold">96.5%</span>
                                <span class="px-1.5 py-0.5 rounded bg-secondary-container text-on-secondary-container font-label-sm text-[10px]">Tercapai</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function filterStatus(status, btnElement) {
        document.querySelectorAll('.status-tab-btn').forEach(btn => {
            btn.classList.remove('bg-primary-container', 'text-on-primary');
            btn.classList.add('bg-surface-container', 'text-on-surface-variant');
        });
        btnElement.classList.remove('bg-surface-container', 'text-on-surface-variant');
        btnElement.classList.add('bg-primary-container', 'text-on-primary');
    }

    function selectPersonnel(element, typeKey) {
        document.querySelectorAll('.personnel-card').forEach(card => {
            card.classList.remove('bg-surface-container-high/80', 'shadow-sm');
            card.classList.add('bg-surface');
        });
        element.classList.remove('bg-surface');
        element.classList.add('bg-surface-container-high/80', 'shadow-sm');
    }
</script>
@endpush