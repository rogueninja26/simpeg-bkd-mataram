<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;

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
            return redirect('/admin/dashboard');
        } elseif ($role === 'Operator_Kepegawaian') {
            return redirect('/operator/dashboard');
        } elseif ($role === 'Pimpinan') {
            return redirect('/pimpinan/dashboard');
        } elseif ($role === 'Pegawai') {
            return redirect('/pegawai/dashboard');
        }

        return "Selamat datang! Role Anda belum diatur. <form action='/logout' method='POST'>".csrf_field()."<button type='submit'>Logout</button></form>";
    })->name('dashboard');

    // Area SuperAdmin
    Route::middleware('role:SuperAdmin')->prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return "Selamat datang, SuperAdmin! <form action='/logout' method='POST'>".csrf_field()."<button type='submit'>Logout</button></form>";
        });
    });

    // Area Operator Kepegawaian
    Route::middleware('role:Operator_Kepegawaian')->prefix('operator')->group(function () {
        Route::get('/dashboard', function () {
            return "Selamat datang, Operator Kepegawaian! <form action='/logout' method='POST'>".csrf_field()."<button type='submit'>Logout</button></form>";
        });
    });

    // Area Pimpinan
    Route::middleware('role:Pimpinan')->prefix('pimpinan')->group(function () {
        Route::get('/dashboard', function () {
            return "Selamat datang, Pimpinan! <form action='/logout' method='POST'>".csrf_field()."<button type='submit'>Logout</button></form>";
        });
    });

    // Area Pegawai
    Route::middleware('role:Pegawai')->prefix('pegawai')->group(function () {
        Route::get('/dashboard', function () {
            return "Selamat datang, Pegawai! <form action='/logout' method='POST'>".csrf_field()."<button type='submit'>Logout</button></form>";
        });
    });
});