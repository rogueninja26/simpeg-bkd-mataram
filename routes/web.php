<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\Monitoring\KontrakKerjaController;
use App\Http\Controllers\Monitoring\KgbController;
use App\Http\Controllers\Monitoring\KenaikanPangkatController;

// Arahkan halaman utama (/) langsung ke /login
Route::get('/', function () {
    return redirect()->route('login');
});

// 1. Area Tamu (Belum Login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// 2. Area Terautentikasi (Sudah Login)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Rute /dashboard otomatis mengarahkan ke dashboard sesuai role pengguna
    Route::get('/dashboard', function () {
        $role = Auth::user()->role->nama_role ?? '';

        if ($role === 'SuperAdmin') {
            return redirect()->route('admin.dashboard');
        } elseif ($role === 'Operator_Kepegawaian') {
            return redirect()->route('operator.dashboard');
        } elseif ($role === 'Pimpinan') {
            return redirect()->route('pimpinan.dashboard');
        } elseif ($role === 'Pegawai') {
            return redirect()->route('pegawai.dashboard');
        }

        return "Selamat datang! Role Anda belum diatur. <form action='/logout' method='POST'>".csrf_field()."<button type='submit'>Logout</button></form>";
    })->name('dashboard');

    // Area SuperAdmin
    Route::middleware('role:SuperAdmin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    });

    // Area Operator Kepegawaian
    Route::middleware('role:Operator_Kepegawaian')->prefix('operator')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('operator.dashboard');
    });

    // Area Pimpinan
    Route::middleware('role:Pimpinan')->prefix('pimpinan')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('pimpinan.dashboard');
    });

    // Area Pegawai (Mandiri)
    Route::middleware('role:Pegawai')->prefix('pegawai')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('pegawai.dashboard');
    });

    // Modul Pengelolaan Data Induk & e-File (Akses Multi-Role selain Pegawai biasa)
    Route::middleware('role:SuperAdmin,Operator_Kepegawaian,Pimpinan')->group(function () {
        Route::get('/data-pegawai', [PegawaiController::class, 'index'])->name('pegawai.index');
    });

    // Aksi Tambah & Edit Data Induk Pegawai (Khusus SuperAdmin & Operator_Kepegawaian)
    Route::middleware('role:SuperAdmin,Operator_Kepegawaian')->group(function () {
        Route::post('/data-pegawai', [PegawaiController::class, 'store'])->name('pegawai.store');
        Route::put('/data-pegawai/{id}', [PegawaiController::class, 'update'])->name('pegawai.update');
        // Manajemen Dokumen e-File
        Route::post('/data-pegawai/{pegawai_id}/efile', [PegawaiController::class, 'uploadEfile'])->name('pegawai.efile.upload');
        Route::patch('/data-pegawai/efile/{id}/verifikasi', [PegawaiController::class, 'verifikasiEfile'])->name('pegawai.efile.verifikasi');
    });

    // 3. Modul Monitoring Kontrak, KGB & Kenaikan Pangkat (Tahap 2)
    Route::prefix('monitoring')->name('monitoring.')->group(function () {
        // Akses Lihat (Semua Role: SuperAdmin, Operator_Kepegawaian, Pimpinan, Pegawai)
        Route::get('/kontrak', [KontrakKerjaController::class, 'index'])->name('kontrak.index');
        Route::get('/kgb', [KgbController::class, 'index'])->name('kgb.index');
        Route::get('/kenaikan-pangkat', [KenaikanPangkatController::class, 'index'])->name('kp.index');

        // Akses CRUD & Perpanjangan (Khusus SuperAdmin & Operator_Kepegawaian)
        Route::middleware('role:SuperAdmin,Operator_Kepegawaian')->group(function () {
            // Kontrak Kerja
            Route::post('/kontrak', [KontrakKerjaController::class, 'store'])->name('kontrak.store');
            Route::put('/kontrak/{id}', [KontrakKerjaController::class, 'update'])->name('kontrak.update');
            Route::delete('/kontrak/{id}', [KontrakKerjaController::class, 'destroy'])->name('kontrak.destroy');

            // KGB
            Route::post('/kgb', [KgbController::class, 'store'])->name('kgb.store');
            Route::put('/kgb/{id}', [KgbController::class, 'update'])->name('kgb.update');
            Route::delete('/kgb/{id}', [KgbController::class, 'destroy'])->name('kgb.destroy');

            // Kenaikan Pangkat
            Route::post('/kenaikan-pangkat', [KenaikanPangkatController::class, 'store'])->name('kp.store');
            Route::put('/kenaikan-pangkat/{id}', [KenaikanPangkatController::class, 'update'])->name('kp.update');
            Route::delete('/kenaikan-pangkat/{id}', [KenaikanPangkatController::class, 'destroy'])->name('kp.destroy');
        });
    });
});