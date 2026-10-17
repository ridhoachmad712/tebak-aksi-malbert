<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function formMasuk(): View
    {
        return view('auth.masuk');
    }

    public function masuk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $kunci = Str::lower($data['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($kunci).' detik.',
            ]);
        }

        if (! Auth::attempt($data, $request->boolean('ingat'))) {
            RateLimiter::hit($kunci);

            throw ValidationException::withMessages(['email' => 'Email atau kata sandi tidak sesuai.']);
        }

        RateLimiter::clear($kunci);
        $user = Auth::user();

        if (! $user->aktif()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => $user->status === 'menunggu'
                    ? 'Akun Anda masih menunggu persetujuan admin.'
                    : 'Akun Anda dinonaktifkan. Hubungi admin sekolah.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('masuk.arah'));
    }

    /** Mengarahkan pengguna ke halaman utama sesuai perannya. */
    public function arah(Request $request): RedirectResponse
    {
        return match ($request->user()->peran) {
            'guru' => redirect()->route('guru.beranda'),
            'kepsek' => redirect()->route('monev'),
            default => redirect('/admin'),
        };
    }

    public function formDaftar(): View
    {
        return view('auth.daftar');
    }

    public function daftar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'jenis_guru' => ['required', 'in:kelas,mapel'],
            'keterangan_daftar' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [], [
            'name' => 'nama', 'jenis_guru' => 'jenis guru', 'keterangan_daftar' => 'kelas/mata pelajaran', 'password' => 'kata sandi',
        ]);

        User::create($data + ['peran' => 'guru', 'status' => 'menunggu']);

        return redirect()->route('daftar')->with('terdaftar', true);
    }

    public function keluar(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('beranda');
    }
}
