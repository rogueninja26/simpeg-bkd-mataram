<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PegawaiController;

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
});