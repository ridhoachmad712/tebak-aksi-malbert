<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Memastikan pengguna aktif dan memiliki salah satu peran yang diizinkan. */
class CekPeran
{
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $user = $request->user();

        if (! $user || ! $user->aktif()) {
            Auth::logout();

            return redirect()->route('login')->with('galat', 'Akun Anda belum aktif. Hubungi admin sekolah.');
        }

        abort_unless(in_array($user->peran, $peran, true), 403, 'Halaman ini tidak tersedia untuk peran Anda.');

        return $next($request);
    }
}
