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
            <button class="flex items-center gap-space-xs px-space-md py-2.5 rounded-xl bg-primary text-on-primary hover:bg-primary-container font-label-lg text-label-lg transition-all shadow-sm" type="button">
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
                    <div class="personnel-card p-space-sm rounded-xl {{ $index === 0 ? 'bg-surface-container-high/80 shadow-sm' : 'bg-surface hover:bg-surface-container-low' }} text-on-surface cursor-pointer transition-all flex items-start gap-space-sm"
                         onclick="selectPersonnelFromDb(this)"
                         data-nama="{{ $namaTampil }}"
                         data-status="{{ $peg->status_teks }}"
                         data-nomor="{{ $peg->nomor_induk ?? $peg->nik }}"
                         data-unit="{{ $peg->nama_unit ?? 'Belum Ditempatkan' }}"
                         data-jabatan="{{ $peg->nama_jabatan ?? '-' }}"
                         data-nik="{{ $peg->nik ?? '-' }}"
                         data-npwp="{{ $peg->npwp ?? '-' }}"
                         data-ttl="{{ ($peg->tempat_lahir ?? '-') . ', ' . ($peg->tgl_lahir ?? '-') }}"
                         data-hp="{{ $peg->no_hp ?? '-' }}"
                         data-email="{{ $peg->email ?? '-' }}"
                         data-alamat="{{ $peg->alamat_domisili ?? '-' }}"
                         data-pola="{{ $peg->nama_pola ?? 'Reguler' }}"
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

        <!-- RIGHT: Active Detail Dossier Profile -->
        @php
            $firstPeg = $pegawaiList->first();
            $firstNama = $firstPeg ? trim(($firstPeg->gelar_depan ? $firstPeg->gelar_depan . ' ' : '') . $firstPeg->nama_lengkap . ($firstPeg->gelar_belakang ? ', ' . $firstPeg->gelar_belakang : '')) : 'Belum Ada Data Pegawai';
        @endphp
        <div class="xl:col-span-8 space-y-space-md">
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

                    @if(in_array(Auth::user()->role->nama_role ?? '', ['SuperAdmin', 'Operator_Kepegawaian']))
                    <div class="flex items-center gap-space-xs shrink-0 self-end md:self-center">
                        <button class="px-3.5 py-2 rounded-xl bg-secondary hover:bg-secondary/90 text-on-secondary font-label-md text-label-md flex items-center gap-1 shadow-sm transition-all" type="button">
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
            card.classList.remove('bg-surface-container-high/80', 'shadow-sm');
            card.classList.add('bg-surface');
        });
        element.classList.remove('bg-surface');
        element.classList.add('bg-surface-container-high/80', 'shadow-sm');

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
    }

    function searchPegawai() {
        const keyword = document.getElementById('pegawaiSearch').value.toLowerCase();
        document.querySelectorAll('.personnel-card').forEach(card => {
            const text = card.innerText.toLowerCase();
            card.style.display = text.includes(keyword) ? 'flex' : 'none';
        });
    }
</script>
@endpush