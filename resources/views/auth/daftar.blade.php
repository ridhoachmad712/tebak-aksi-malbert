<x-layouts.auth judul="Daftar Guru">
    @if (session('terdaftar'))
        <div class="text-center">
            <div class="text-5xl">🎉</div>
            <h1 class="mt-3 text-2xl font-extrabold">Pendaftaran terkirim</h1>
            <p class="mt-2 text-slate-600">Akun Anda sedang <b>menunggu persetujuan admin</b>. Setelah disetujui, Anda dapat masuk dengan email dan kata sandi yang didaftarkan.</p>
            <a href="{{ route('login') }}" class="tombol-utama mt-6 w-full">Ke halaman masuk</a>
        </div>
    @else
        <h1 class="text-2xl font-extrabold">Daftar sebagai guru</h1>
        <p class="mt-1 text-sm text-slate-500">Akun akan aktif setelah disetujui admin sekolah.</p>

        <form method="POST" action="{{ route('daftar') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="name" class="label">Nama lengkap (beserta gelar)</label>
                <input id="name" name="name" value="{{ old('name') }}" required class="isian">
                @error('name') <p class="galat">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" class="isian">
                @error('email') <p class="galat">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="telepon" class="label">Nomor HP/WA <span class="font-normal text-slate-400">(opsional)</span></label>
                <input id="telepon" name="telepon" value="{{ old('telepon') }}" inputmode="tel" class="isian">
                @error('telepon') <p class="galat">{{ $message }}</p> @enderror
            </div>
            <div>
                <span class="label">Saya adalah</span>
                <div class="grid grid-cols-2 gap-2">
                    @foreach (\App\Models\User::JENIS_GURU as $nilai => $label)
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-300 p-3 text-sm font-bold has-checked:border-brand-400 has-checked:bg-brand-50">
                            <input type="radio" name="jenis_guru" value="{{ $nilai }}" @checked(old('jenis_guru', 'kelas') === $nilai) class="accent-brand-500"> {{ $label }}
                        </label>
                    @endforeach
                </div>
                @error('jenis_guru') <p class="galat">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="keterangan_daftar" class="label">Kelas / mata pelajaran yang diampu</label>
                <input id="keterangan_daftar" name="keterangan_daftar" value="{{ old('keterangan_daftar') }}" placeholder="Contoh: Wali Kelas 4A, atau Guru PJOK kelas IV–VI" required class="isian">
                @error('keterangan_daftar') <p class="galat">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password" class="label">Kata sandi</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" class="isian">
                <p class="mt-1 text-xs text-slate-500">Minimal 8 karakter.</p>
                @error('password') <p class="galat">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password_confirmation" class="label">Ulangi kata sandi</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="isian">
            </div>
            <button class="tombol-utama w-full py-3 text-base">Kirim Pendaftaran</button>
        </form>

        <p class="mt-6 border-t border-slate-100 pt-5 text-center text-sm text-slate-600">Sudah punya akun? <a href="{{ route('login') }}" class="tautan">Masuk</a></p>
    @endif
</x-layouts.auth>
