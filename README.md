# SIMPEG BKD Kota Mataram

**Sistem Informasi Manajemen Kepegawaian (SIMPEG)** untuk lingkungan **Badan Keuangan Daerah (BKD) Kota Mataram**. Aplikasi berbasis web ini dirancang untuk mengelola data induk kepegawaian, arsip digital (e-File), pemantauan *bezetting* SDM, serta pengingat masa kontrak, Kenaikan Gaji Berkala (KGB), dan Kenaikan Pangkat (KP).

---

## Teknologi yang Digunakan

* **Backend Framework:** Laravel 8.83.29
* **Bahasa Pemrograman:** PHP 7.4.24 (XAMPP)
* **Database:** PostgreSQL 19 (`simpeg_bkd_mataram`)
* **Frontend:** Laravel Blade & Tailwind CSS
* **Autentikasi & Otorisasi:** Custom Laravel Auth & Role-Based Access Control (RBAC)

---

## Hak Akses Pengguna (RBAC)

Sistem menerapkan pembatasan akses berbasis 4 peran (*role*) utama yang terhubung dengan data pegawai:

1. **SuperAdmin** — Pengelolaan penuh sistem, manajemen akun pengguna, dan konfigurasi referensi master.
2. **Operator_Kepegawaian** — Pengelolaan data induk pegawai, validasi e-File, serta pemrosesan administrasi KGB/KP.
3. **Pimpinan** — Pemantauan dashboard *bezetting* SDM, statistik kepegawaian, dan laporan eksekutif.
4. **Pegawai** — Akses mandiri (*self-service*) untuk melihat profil kepegawaian, status berkas, dan riwayat administrasi pribadi.

---

## Panduan Instalasi Lokal

1. **Clone repositori ini:**
   ```bash
   git clone [https://github.com/username-anda/simpeg-bkd-mataram.git](https://github.com/username-anda/simpeg-bkd-mataram.git)
   cd simpeg-bkd-mataram
