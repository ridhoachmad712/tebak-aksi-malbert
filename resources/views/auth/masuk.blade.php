<x-layouts.auth judul="Masuk">
    <h1 class="text-2xl font-extrabold">Masuk</h1>
    <p class="teks-bantu mt-1">Untuk guru, kepala sekolah, dan admin.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="isian">
            @error('email') <p class="galat">{{ $message }}</p> @enderror
        </div>
        <div x-data="{ lihat: false }">
            <label for="password" class="label">Kata sandi</label>
            <div class="relative">
                <input id="password" name="password" :type="lihat ? 'text' : 'password'" required autocomplete="current-password" class="isian pr-12">
                <button type="button" x-on:click="lihat = !lihat" class="absolute inset-y-0 right-0 px-3 text-slate-500 hover:text-slate-800" aria-label="Tampilkan kata sandi">
                    <x-heroicon-o-eye class="h-5 w-5" x-show="!lihat" />
                    <x-heroicon-o-eye-slash class="h-5 w-5" x-show="lihat" x-cloak />
                </button>
            </div>
        </div>
        <label class="flex items-center gap-2 text-sm font-semibold text-slate-600">
            <input type="checkbox" name="ingat" class="h-4 w-4 rounded accent-brand-700"> Ingat saya
        </label>
        <button class="tombol-utama w-full text-base">Masuk</button>
    </form>

    <div class="mt-6 space-y-2 border-t border-garis pt-5 text-sm text-slate-600">
        <p>Belum punya akun? <a href="{{ route('daftar') }}" class="tautan">Daftar sebagai guru</a></p>
        <p>Lupa kata sandi? Hubungi admin sekolah untuk mengatur ulang.</p>
    </div>
</x-layouts.auth>
