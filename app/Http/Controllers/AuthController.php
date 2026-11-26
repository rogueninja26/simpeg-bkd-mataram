<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuthController extends Controller
{
    // Menampilkan halaman form login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Memproses data login
    public function login(Request $request)
    {
        // 1. Validasi input form
        $credentials = $request->validate([
            'username_login' => 'required|string',
            'password' => 'required|string',
        ], [
            'username_login.required' => 'NIP / Username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        // 2. Cek kecocokan username_login & password
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // 3. Cek apakah akun aktif
            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'username_login' => 'Akun Anda dinonaktifkan. Silakan hubungi Admin BKD.',
                ])->onlyInput('username_login');
            }

            // 4. Regenerasi session untuk mencegah serangan Session Fixation
            $request->session()->regenerate();

            // 5. Update waktu terakhir login
            $user->update([
                'last_login' => Carbon::now()
            ]);

            // 6. Arahkan ke dashboard sesuai role
            return $this->redirectBasedOnRole($user);
        }

        // Jika NIP atau password salah
        return back()->withErrors([
            'username_login' => 'NIP atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('username_login');
    }

    // Logika pengalihan halaman berdasarkan role
    protected function redirectBasedOnRole($user)
    {
        $role = $user->role->nama_role ?? '';

        switch ($role) {
            case 'SuperAdmin':
                return redirect()->intended('/admin/dashboard');
            case 'Operator_Kepegawaian':
                return redirect()->intended('/operator/dashboard');
            case 'Pimpinan':
                return redirect()->intended('/pimpinan/dashboard');
            case 'Pegawai':
                return redirect()->intended('/pegawai/dashboard');
            default:
                Auth::logout();
                return redirect('/login')->withErrors(['username_login' => 'Role pengguna tidak valid.']);
        }
    }

    // Proses Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}