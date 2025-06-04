<?php

// app/Http/Middleware/CheckUserRole.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // Pastikan ini ada

class CheckUserRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // PENTING: Periksa apakah pengguna sudah login DULU
        if (!Auth::check()) {
            return redirect('/login'); // Arahkan ke halaman login jika belum login
        }

        // Jika sudah login, barulah kita bisa mengakses role-nya
        // Pastikan pengguna memiliki peran yang diizinkan
        if (!in_array(Auth::user()->role, $roles)) {
            abort(403, 'Unauthorized action.'); // Beri pesan error 403 jika tidak diizinkan
        }

        return $next($request);
    }
}