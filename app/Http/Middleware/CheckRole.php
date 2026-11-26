<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Pastikan user sudah login
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        $userRole = $user->role->nama_role ?? null;

        // Periksa apakah role user termasuk dalam daftar role yang diizinkan pada rute ini
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // Jika tidak punya akses, tampilkan halaman error 403 (Forbidden)
        abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
    }
}