<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SIMPEG BKD Kota Mataram</title>

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,400,0,0" rel="stylesheet" />

    <!-- Tailwind CSS & Token Desain Lombok Sasak Governance -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ asset('js/tailwind-config.js') }}"></script>

    <style>
        .tabular-nums { font-variant-numeric: tabular-nums; }
    </style>
</head>
<body class="bg-[#f8fafc] min-h-screen flex items-center justify-center p-4 font-sans antialiased text-[#0f172a]">

    <div class="w-full max-w-4xl bg-white rounded-card border border-[#e2e8f0] shadow-[0_10px_15px_-3px_rgba(15,41,66,0.08),0_4px_6px_-4px_rgba(15,41,66,0.04)] overflow-hidden grid grid-cols-1 md:grid-cols-12">

        <!-- Panel Kiri: Maritime Navy (#0f2942) + Aksen Sasak Amber Gold (#d97706) -->
        <div class="md:col-span-5 bg-primary text-white p-8 flex flex-col justify-between relative overflow-hidden">
            <div class="relative z-10">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-[11px] font-bold tracking-wider uppercase mb-6 border border-white/15">
                    <span class="material-symbols-outlined text-sm text-tertiary">verified_user</span>
                    <span>PORTAL KEPEGAWAIAN</span>
                </div>

                <h1 class="font-display text-2xl font-bold tracking-tight leading-snug mb-2">
                    SIMPEG BKD<br>Kota Mataram
                </h1>
                <p class="text-sm text-[#b0c9e8] leading-relaxed">
                    Sistem Informasi Manajemen Kepegawaian Terpadu — Data Induk e-File, Bezetting SDM, dan Monitoring KGB/KP.
                </p>
            </div>

            <div class="relative z-10 mt-8 pt-6 border-t border-white/15 space-y-3 text-xs text-[#e6eeff]">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-base text-secondary">shield_person</span>
                    <span>Akses Terproteksi 4 Role (RBAC)</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-base text-tertiary">account_balance</span>
                    <span>Badan Keuangan Daerah Kota Mataram</span>
                </div>
            </div>

            <!-- Aksen Garis Geometris Halus -->
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-secondary via-tertiary to-primary"></div>
        </div>

        <!-- Panel Kanan: Form Autentikasi -->
        <div class="md:col-span-7 p-8 sm:p-10 flex flex-col justify-center bg-white">
            <div class="mb-6">
                <h2 class="font-display text-xl font-bold text-[#0f2942]">Masuk ke Sistem</h2>
                <p class="text-sm text-[#43474d] mt-1">Gunakan NIP atau Username terdaftar untuk mengakses layanan kepegawaian.</p>
            </div>

            <!-- Notifikasi Sukses (Logout) -->
            @if(session('success'))
                <div class="mb-5 p-3.5 rounded bg-[#ccfbf1] border border-[#5eead4] text-[#0f766e] text-sm flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-lg shrink-0">check_circle</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Notifikasi Error Validasi / Gagal Login -->
            @if($errors->any())
                <div class="mb-5 p-3.5 rounded bg-[#ffdad6] border border-[#ba1a1a]/30 text-[#93000a] text-sm flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-lg text-[#dc2626] shrink-0">error</span>
                    <ul class="space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Input NIP / Username (Tabular Nums + Focus Ring Emerald Teal #0d9488) -->
                <div>
                    <label for="username_login" class="block text-xs font-semibold uppercase tracking-wider text-[#334155] mb-1.5">
                        NIP / Username Login
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined text-[#74777e] text-xl absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                            badge
                        </span>
                        <input type="text"
                               id="username_login"
                               name="username_login"
                               value="{{ old('username_login') }}"
                               placeholder="199511102020122012"
                               required
                               autofocus
                               class="tabular-nums w-full pl-10 pr-3 py-2 text-sm bg-white border rounded text-[#0f172a] placeholder-[#94a3b8] focus:outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/20 transition {{ $errors->has('username_login') ? 'border-[#dc2626]' : 'border-[#cbd5e1]' }}">
                    </div>
                </div>

                <!-- Input Kata Sandi -->
                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-[#334155] mb-1.5">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined text-[#74777e] text-xl absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                            lock
                        </span>
                        <input type="password"
                               id="password"
                               name="password"
                               placeholder="Masukkan kata sandi"
                               required
                               class="w-full pl-10 pr-10 py-2 text-sm bg-white border rounded text-[#0f172a] placeholder-[#94a3b8] focus:outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/20 transition {{ $errors->has('password') ? 'border-[#dc2626]' : 'border-[#cbd5e1]' }}">
                        <button type="button"
                                onclick="togglePassword()"
                                title="Tampilkan/Sembunyikan Kata Sandi"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[#74777e] hover:text-[#334155] focus:outline-none">
                            <span id="eye-icon" class="material-symbols-outlined text-xl">visibility</span>
                        </button>
                    </div>
                </div>

                <!-- Tombol Primary (#0f2942, radius 4px) -->
                <div class="pt-2">
                    <button type="submit"
                            class="w-full py-2.5 px-4 bg-primary hover:bg-[#1e3a8a] text-white font-semibold text-sm rounded transition flex items-center justify-center gap-2 cursor-pointer">
                        <span>Masuk ke Sistem</span>
                        <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-5 border-t border-[#f1f5f9] text-center space-y-1">
                <p class="text-xs text-[#43474d]">
                    Kendala akun atau lupa kata sandi? Hubungi <span class="font-semibold text-primary">Admin Kepegawaian BKD</span>.
                </p>
                <p class="text-[11px] text-[#74777e]">
                    &copy; {{ date('Y') }} Badan Keuangan Daerah Kota Mataram
                </p>
            </div>
        </div>

    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility';
            }
        }
    </script>
</body>
</html>