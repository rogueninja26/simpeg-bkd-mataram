<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'SIMPEG BKD Kota Mataram')</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    <!-- Tailwind CSS & Konfigurasi Terpusat -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ asset('js/tailwind-config.js') }}"></script>

    <style>
        @layer base {
            html, body { margin: 0; padding: 0; }
            body { overscroll-behavior: none; }
            main > :first-child { margin-top: 0 !important; }
            main > :last-child { margin-bottom: 0 !important; }
        }
        ::-webkit-scrollbar { display: none; }
    </style>
    @stack('styles')
</head>
<body class="bg-surface font-body-md text-on-surface antialiased">

    @php
        $user = Auth::user();
        $userRole = $user->role->nama_role ?? 'Pegawai';
        $namaLengkap = $user->pegawai->nama_lengkap ?? $user->username_login ?? 'Pengguna SIMPEG';
        $nipDisplay = $user->username_login ?? '-';

        $alerts = $monitoringAlerts ?? [
            'total'   => 0,
            'kontrak' => 0,
            'kgb'     => 0,
            'kp'      => 0,
            'items'   => collect(),
        ];
        $isMonitoringRoute = request()->routeIs('monitoring.*');
    @endphp

    <!-- SIDEBAR -->
    <aside id="sidebar" class="fixed left-0 top-0 h-full w-64 bg-surface-container-lowest shadow-[0_1px_8px_rgba(0,0,0,0.04)] z-50 flex flex-col justify-between transition-transform duration-200 -translate-x-full lg:translate-x-0">
        <div class="flex flex-col overflow-y-auto">
            <!-- Brand Header -->
            <div class="h-16 px-space-md flex items-center justify-between bg-surface-container-low shrink-0">
                <div class="flex items-center gap-space-sm min-w-0">
                    <div class="w-9 h-9 rounded-lg bg-primary-container flex items-center justify-center text-on-primary font-headline-sm font-bold shadow-sm shrink-0">
                        <span class="material-symbols-outlined text-[20px] text-on-primary">account_balance</span>
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="font-headline-sm text-headline-sm text-primary tracking-tight truncate leading-tight">SIMPEG BKD</span>
                        <span class="font-label-sm text-label-sm text-on-surface-variant truncate uppercase tracking-wider">Kota Mataram</span>
                    </div>
                </div>
                <button type="button" onclick="toggleSidebar()" class="lg:hidden p-1 rounded-lg text-on-surface-variant hover:bg-surface-container-high">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <!-- Label Kategori Menu -->
            <div class="px-space-md pt-space-md pb-space-xs">
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">
                    Menu {{ str_replace('_', ' ', $userRole) }}
                </span>
            </div>

            <!-- Navigasi Dinamis Berdasarkan Peran -->
            <nav class="px-space-sm flex flex-col gap-space-xs">

                {{-- 1. MENU DASHBOARD (Semua Peran) --}}
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-space-sm px-space-md py-space-sm rounded-xl font-label-lg text-label-lg transition-all {{ request()->routeIs('*.dashboard') || request()->routeIs('dashboard') ? 'bg-primary-container text-on-primary shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                    <span class="material-symbols-outlined text-[20px]">analytics</span>
                    <span class="truncate">Dashboard & Bezetting</span>
                </a>

                {{-- 2. MENU PENGELOLAAN KEPEGAWAIAN (SuperAdmin, Operator_Kepegawaian, Pimpinan) --}}
                @if(in_array($userRole, ['SuperAdmin', 'Operator_Kepegawaian', 'Pimpinan']))
                    <a href="{{ route('pegawai.index') }}"
                       class="flex items-center gap-space-sm px-space-md py-space-sm rounded-xl font-label-lg text-label-lg transition-all {{ request()->routeIs('pegawai.*') ? 'bg-primary-container text-on-primary shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                        <span class="material-symbols-outlined text-[20px]">badge</span>
                        <span class="truncate">Data Induk & e-File</span>
                    </a>

                    {{-- Dropdown / Sub-menu Monitoring Kontrak, KGB & KP --}}
                    <div class="flex flex-col gap-1">
                        <button type="button" onclick="toggleMonitoringMenu()"
                                class="w-full flex items-center justify-between px-space-md py-space-sm rounded-xl font-label-lg text-label-lg transition-all {{ $isMonitoringRoute ? 'bg-secondary-container text-on-secondary-container font-semibold' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                            <div class="flex items-center gap-space-sm min-w-0">
                                <span class="material-symbols-outlined text-[20px]">event_upcoming</span>
                                <span class="truncate">Kontrak, KGB & KP</span>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                @if($alerts['total'] > 0)
                                    <span class="px-1.5 py-0.5 rounded-full bg-error text-on-error font-label-sm text-[10px] font-bold">
                                        {{ $alerts['total'] }}
                                    </span>
                                @endif
                                <span id="monitoring-chevron" class="material-symbols-outlined text-[18px] transition-transform duration-200 {{ $isMonitoringRoute ? 'rotate-180' : '' }}">expand_more</span>
                            </div>
                        </button>

                        <div id="monitoring-submenu" class="{{ $isMonitoringRoute ? 'flex' : 'hidden' }} flex-col gap-1 pl-4 pr-1 py-1">
                            <a href="{{ route('monitoring.kontrak.index') }}"
                               class="flex items-center justify-between px-space-md py-2 rounded-lg font-label-md text-label-md transition-all {{ request()->routeIs('monitoring.kontrak.*') ? 'bg-primary-container text-on-primary shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                                <span class="truncate">• Kontrak Kerja</span>
                                @if($alerts['kontrak'] > 0)
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ request()->routeIs('monitoring.kontrak.*') ? 'bg-on-primary text-primary' : 'bg-error-container text-on-error-container' }}">
                                        {{ $alerts['kontrak'] }}
                                    </span>
                                @endif
                            </a>

                            <a href="{{ route('monitoring.kgb.index') }}"
                               class="flex items-center justify-between px-space-md py-2 rounded-lg font-label-md text-label-md transition-all {{ request()->routeIs('monitoring.kgb.*') ? 'bg-primary-container text-on-primary shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                                <span class="truncate">• Monitoring KGB</span>
                                @if($alerts['kgb'] > 0)
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ request()->routeIs('monitoring.kgb.*') ? 'bg-on-primary text-primary' : 'bg-error-container text-on-error-container' }}">
                                        {{ $alerts['kgb'] }}
                                    </span>
                                @endif
                            </a>

                            <a href="{{ route('monitoring.kp.index') }}"
                               class="flex items-center justify-between px-space-md py-2 rounded-lg font-label-md text-label-md transition-all {{ request()->routeIs('monitoring.kp.*') ? 'bg-primary-container text-on-primary shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                                <span class="truncate">• Kenaikan Pangkat</span>
                                @if($alerts['kp'] > 0)
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ request()->routeIs('monitoring.kp.*') ? 'bg-on-primary text-primary' : 'bg-error-container text-on-error-container' }}">
                                        {{ $alerts['kp'] }}
                                    </span>
                                @endif
                            </a>
                        </div>
                    </div>
                @endif

                {{-- 3. MENU LAYANAN MANDIRI PEGAWAI (Khusus Pegawai) --}}
                @if($userRole === 'Pegawai')
                    <a href="#"
                       class="flex items-center gap-space-sm px-space-md py-space-sm rounded-xl font-label-lg text-label-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-all">
                        <span class="material-symbols-outlined text-[20px]">folder_shared</span>
                        <span class="truncate">Profil & e-File Saya</span>
                    </a>
                    <a href="{{ route('monitoring.kontrak.index') }}"
                       class="flex items-center gap-space-sm px-space-md py-space-sm rounded-xl font-label-lg text-label-lg transition-all {{ request()->routeIs('monitoring.kontrak.*') ? 'bg-primary-container text-on-primary shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                        <span class="material-symbols-outlined text-[20px]">description</span>
                        <span class="truncate">Riwayat Kontrak Saya</span>
                    </a>
                    <a href="{{ route('monitoring.kgb.index') }}"
                       class="flex items-center gap-space-sm px-space-md py-space-sm rounded-xl font-label-lg text-label-lg transition-all {{ request()->routeIs('monitoring.kgb.*') ? 'bg-primary-container text-on-primary shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                        <span class="material-symbols-outlined text-[20px]">trending_up</span>
                        <span class="truncate">Riwayat KGB Saya</span>
                    </a>
                    <a href="{{ route('monitoring.kp.index') }}"
                       class="flex items-center gap-space-sm px-space-md py-space-sm rounded-xl font-label-lg text-label-lg transition-all {{ request()->routeIs('monitoring.kp.*') ? 'bg-primary-container text-on-primary shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                        <span class="material-symbols-outlined text-[20px]">military_tech</span>
                        <span class="truncate">Riwayat Pangkat Saya</span>
                    </a>
                @endif

                {{-- 4. MENU OPERASIONAL HARIAN (Semua Peran) --}}
                <a href="#"
                   class="flex items-center gap-space-sm px-space-md py-space-sm rounded-xl font-label-lg text-label-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-all">
                    <span class="material-symbols-outlined text-[20px]">date_range</span>
                    <span class="truncate">Cuti, Izin & Presensi</span>
                </a>

                <a href="#"
                   class="flex items-center gap-space-sm px-space-md py-space-sm rounded-xl font-label-lg text-label-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-all">
                    <span class="material-symbols-outlined text-[20px]">flight_takeoff</span>
                    <span class="truncate">Perjalanan Dinas (SPPD)</span>
                </a>

                {{-- 5. MENU KEUANGAN / TPP (SuperAdmin, Operator_Kepegawaian, Pimpinan) --}}
                @if(in_array($userRole, ['SuperAdmin', 'Operator_Kepegawaian', 'Pimpinan']))
                    <a href="#"
                       class="flex items-center gap-space-sm px-space-md py-space-sm rounded-xl font-label-lg text-label-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-all">
                        <span class="material-symbols-outlined text-[20px]">payments</span>
                        <span class="truncate">Rekap TPP & Honorarium</span>
                    </a>
                @endif

                {{-- 6. MENU ADMINISTRASI SISTEM (Khusus SuperAdmin) --}}
                @if($userRole === 'SuperAdmin')
                    <div class="px-space-sm pt-space-md pb-space-xs">
                        <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Administrasi Sistem</span>
                    </div>
                    <a href="#"
                       class="flex items-center gap-space-sm px-space-md py-space-sm rounded-xl font-label-lg text-label-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-all">
                        <span class="material-symbols-outlined text-[20px]">admin_panel_settings</span>
                        <span class="truncate">Manajemen Akun & Peran</span>
                    </a>
                    <a href="#"
                       class="flex items-center gap-space-sm px-space-md py-space-sm rounded-xl font-label-lg text-label-lg text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-all">
                        <span class="material-symbols-outlined text-[20px]">database</span>
                        <span class="truncate">Master Referensi BKN</span>
                    </a>
                @endif
            </nav>
        </div>

        <!-- Status Integrasi Footer Sidebar -->
        <div class="p-space-md bg-surface-container-low shrink-0">
            <div class="p-space-sm rounded-lg bg-surface-container-lowest shadow-[0_1px_8px_rgba(0,0,0,0.04)] flex flex-col gap-space-xs">
                <div class="flex items-center justify-between">
                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Basis Data</span>
                    <span class="w-2 h-2 rounded-full bg-secondary"></span>
                </div>
                <span class="font-tabular-data text-tabular-data text-on-surface font-semibold">PostgreSQL 19 Aktif</span>
                <span class="font-body-sm text-body-sm text-outline text-[11px]">
                    Login: {{ $user->last_login ? \Carbon\Carbon::parse($user->last_login)->format('d M Y, H:i') : 'Baru saja' }}
                </span>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT WRAPPER -->
    <div class="lg:pl-64 flex flex-col min-h-screen">
        <!-- TOPBAR -->
        <header class="fixed top-0 left-0 lg:left-64 right-0 h-16 bg-surface-container-lowest/90 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)] z-40">
            <div class="w-full h-16 px-space-md lg:px-gutter-desktop flex items-center justify-between">

                <!-- Kiri: Hamburger Mobile & Identitas Instansi -->
                <div class="flex items-center gap-space-md">
                    <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 rounded-xl text-on-surface-variant hover:bg-surface-container-high">
                        <span class="material-symbols-outlined text-[22px]">menu</span>
                    </button>
                    <div class="flex items-center gap-space-xs">
                        <span class="material-symbols-outlined text-primary text-[24px] hidden sm:inline">shield_person</span>
                        <div class="flex flex-col">
                            <h1 class="font-headline-sm text-headline-sm text-primary leading-tight font-bold">SIMPEG BKD KOTA MATARAM</h1>
                            <span class="font-label-sm text-label-sm text-on-surface-variant hidden sm:inline">Badan Keuangan Daerah Kota Mataram</span>
                        </div>
                    </div>
                    <div class="hidden xl:inline-flex items-center gap-space-xs px-space-sm py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-label-sm text-label-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-secondary animate-pulse"></span>
                        <span>Peran: {{ $userRole }}</span>
                    </div>
                </div>

                <!-- Kanan: Notifikasi Lonceng, Profil User & Logout -->
                <div class="flex items-center gap-space-sm md:gap-space-md">
                    @if(in_array($userRole, ['SuperAdmin', 'Operator_Kepegawaian', 'Pimpinan']))
                        <div class="relative" id="notification-wrapper">
                            <button type="button" id="notification-btn" onclick="toggleNotificationDropdown(event)"
                                    title="Peringatan Kontrak, KGB & Kenaikan Pangkat"
                                    class="relative p-2 rounded-xl text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors flex items-center justify-center">
                                <span class="material-symbols-outlined text-[22px]">notifications</span>
                                @if($alerts['total'] > 0)
                                    <span class="absolute top-1 right-1 flex h-4 min-w-[16px] px-1 items-center justify-center rounded-full bg-error text-on-error font-label-sm text-[10px] font-bold shadow-sm">
                                        {{ $alerts['total'] > 99 ? '99+' : $alerts['total'] }}
                                    </span>
                                @endif
                            </button>

                            <!-- Dropdown Panel Notifikasi -->
                            <div id="notification-dropdown"
                                 class="hidden absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-surface-container-lowest shadow-[0_8px_28px_rgba(0,0,0,0.12)] border border-outline-variant/40 overflow-hidden z-50">
                                <!-- Header Dropdown -->
                                <div class="px-4 py-3 bg-surface-container-low border-b border-outline-variant/30 flex items-center justify-between">
                                    <div>
                                        <h3 class="font-label-lg text-label-lg text-on-surface font-bold">Peringatan Jatuh Tempo</h3>
                                        <p class="font-label-sm text-label-sm text-on-surface-variant">Kontrak, KGB & Proyeksi Kenaikan Pangkat</p>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full bg-primary-container text-on-primary font-label-sm text-[11px] font-bold">
                                        {{ $alerts['total'] }} Total
                                    </span>
                                </div>

                                <!-- Ringkasan Kategori Filter Cepat -->
                                <div class="grid grid-cols-3 divide-x divide-outline-variant/30 bg-surface-container-lowest border-b border-outline-variant/30 text-center">
                                    <a href="{{ route('monitoring.kontrak.index') }}" class="py-2 px-1 hover:bg-surface-container-high transition-colors">
                                        <span class="block font-tabular-data text-sm font-bold text-on-surface">{{ $alerts['kontrak'] }}</span>
                                        <span class="block font-label-sm text-[10px] text-on-surface-variant uppercase">Kontrak</span>
                                    </a>
                                    <a href="{{ route('monitoring.kgb.index', ['filter' => 'jatuh_tempo']) }}" class="py-2 px-1 hover:bg-surface-container-high transition-colors">
                                        <span class="block font-tabular-data text-sm font-bold text-on-surface">{{ $alerts['kgb'] }}</span>
                                        <span class="block font-label-sm text-[10px] text-on-surface-variant uppercase">KGB</span>
                                    </a>
                                    <a href="{{ route('monitoring.kp.index', ['filter' => 'proyeksi']) }}" class="py-2 px-1 hover:bg-surface-container-high transition-colors">
                                        <span class="block font-tabular-data text-sm font-bold text-on-surface">{{ $alerts['kp'] }}</span>
                                        <span class="block font-label-sm text-[10px] text-on-surface-variant uppercase">Proyeksi KP</span>
                                    </a>
                                </div>

                                <!-- Daftar Item Peringatan -->
                                <div class="max-h-80 overflow-y-auto divide-y divide-outline-variant/20">
                                    @forelse($alerts['items'] as $item)
                                        @php
                                            $isUrgent = $item['sisa_hari'] <= 30;
                                            $badgeMap = [
                                                'KONTRAK' => 'bg-error-container text-on-error-container',
                                                'KGB'     => 'bg-secondary-container text-on-secondary-container',
                                            ];
                                            $badgeClass = $badgeMap[$item['kategori']] ?? 'bg-primary-container text-on-primary';
                                        @endphp
                                        <a href="{{ $item['url'] }}" class="flex items-start gap-3 p-3.5 hover:bg-surface-container-high/60 transition-colors">
                                            <span class="mt-0.5 px-2 py-0.5 rounded-md font-label-sm text-[10px] font-bold shrink-0 {{ $badgeClass }}">
                                                {{ $item['kategori'] }}
                                            </span>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-label-md text-label-md text-on-surface font-semibold truncate">
                                                    {{ $item['pegawai'] }}
                                                </p>
                                                <p class="font-label-sm text-[11px] text-on-surface-variant font-tabular-data">
                                                    NIP. {{ $item['nip'] }}
                                                </p>
                                                <p class="mt-0.5 font-body-sm text-xs text-on-surface-variant">
                                                    {{ $item['detail'] }}
                                                </p>
                                            </div>
                                            <span class="shrink-0 px-2 py-0.5 rounded-full font-tabular-data text-[11px] font-semibold {{ $isUrgent ? 'bg-error text-on-error' : 'bg-surface-container-high text-on-surface-variant' }}">
                                                {{ $item['sisa_hari'] <= 0 ? 'Hari ini' : 'H-' . $item['sisa_hari'] }}
                                            </span>
                                        </a>
                                    @empty
                                        <div class="py-8 px-4 text-center flex flex-col items-center gap-1">
                                            <span class="material-symbols-outlined text-secondary text-[28px]">check_circle</span>
                                            <span class="font-label-md text-on-surface font-medium">Tidak ada jatuh tempo mendekat</span>
                                            <span class="font-body-sm text-xs text-on-surface-variant">Semua kontrak, KGB, dan KP dalam batas aman.</span>
                                        </div>
                                    @endforelse
                                </div>

                                <!-- Footer Dropdown -->
                                <div class="px-4 py-2.5 bg-surface-container-low border-t border-outline-variant/30 flex items-center justify-between text-xs">
                                    <a href="{{ route('monitoring.kontrak.index') }}" class="font-label-md text-primary font-semibold hover:underline">Kontrak</a>
                                    <a href="{{ route('monitoring.kgb.index') }}" class="font-label-md text-primary font-semibold hover:underline">Semua KGB</a>
                                    <a href="{{ route('monitoring.kp.index') }}" class="font-label-md text-primary font-semibold hover:underline">Semua KP</a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="h-6 w-px bg-outline-variant"></div>

                    <!-- Info Pengguna -->
                    <div class="flex items-center gap-space-sm">
                        <div class="flex flex-col text-right hidden md:flex">
                            <span class="font-label-md text-label-md text-on-surface font-semibold truncate max-w-[160px]">{{ $namaLengkap }}</span>
                            <span class="font-label-sm text-label-sm text-on-surface-variant font-tabular-data">NIP. {{ $nipDisplay }}</span>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center shadow-sm text-on-primary font-label-sm">
                            {{ strtoupper(substr($namaLengkap, 0, 1)) }}
                        </div>
                    </div>

                    <!-- Tombol Logout -->
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" title="Keluar Aplikasi" class="p-2 rounded-xl text-error hover:bg-error-container hover:text-on-error-container transition-colors flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">logout</span>
                        </button>
                    </form>
                </div>

            </div>
        </header>

        <!-- KONTEN DINAMIS -->
        <main class="w-full pt-20 px-space-md lg:px-gutter-desktop flex-1 bg-surface">
            @if(session('success'))
                <div class="mb-space-md p-space-md rounded-xl bg-secondary-container text-on-secondary-container font-label-md flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" class="text-xs font-bold">Tutup</button>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-space-md p-space-md rounded-xl bg-error-container text-on-error-container font-label-md">
                    <div class="font-bold mb-1">Terjadi kesalahan validasi:</div>
                    <ul class="list-disc list-inside text-xs space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        }

        function toggleMonitoringMenu() {
            const submenu = document.getElementById('monitoring-submenu');
            const chevron = document.getElementById('monitoring-chevron');
            if (submenu && chevron) {
                submenu.classList.toggle('hidden');
                submenu.classList.toggle('flex');
                chevron.classList.toggle('rotate-180');
            }
        }

        function toggleNotificationDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('notification-dropdown');
            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
        }

        // Tutup dropdown notifikasi jika klik di luar panel
        document.addEventListener('click', function (event) {
            const wrapper = document.getElementById('notification-wrapper');
            const dropdown = document.getElementById('notification-dropdown');
            if (wrapper && dropdown && !wrapper.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>