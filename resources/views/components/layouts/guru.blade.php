@php
    $peran = auth()->user()->peran;
    $menu = [
        ['route' => 'guru.beranda', 'aktif' => 'guru.beranda', 'label' => 'Beranda', 'ikon' => 'heroicon-o-home'],
        ['route' => 'guru.kegiatan', 'aktif' => 'guru.kegiatan*', 'label' => 'Kegiatan Harian', 'pendek' => 'Harian', 'ikon' => 'heroicon-o-check-circle'],
        ['route' => 'guru.pembinaan', 'aktif' => 'guru.pembinaan*', 'label' => 'Pembinaan', 'ikon' => 'heroicon-o-heart'],
        ['route' => 'guru.karya', 'aktif' => 'guru.karya*', 'label' => 'Karya Literasi', 'pendek' => 'Karya', 'ikon' => 'heroicon-o-paint-brush'],
        ['route' => 'guru.rekap', 'aktif' => 'guru.rekap*', 'label' => 'Rekap', 'ikon' => 'heroicon-o-chart-bar'],
    ];
    $monev = ['route' => 'monev', 'aktif' => 'monev', 'label' => 'Monev', 'ikon' => 'heroicon-o-presentation-chart-line'];
    if ($peran === 'admin') {
        $menu[0] = $monev;
    } elseif ($peran === 'kepsek') {
        $menu = [$monev, end($menu)];
    }
    $beranda = $peran === 'guru' ? route('guru.beranda') : route('monev');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f97316">
    <title>{{ isset($judul) ? $judul.' · ' : '' }}TEBAK AKSI</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen pb-24 md:pb-10">
    <x-notifikasi />

    <header class="sticky top-0 z-30 border-b border-orange-100 bg-white/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4">
            <a href="{{ $beranda }}"><x-logo kecil /></a>

            <nav class="hidden items-center gap-1 md:flex">
                @foreach ($menu as $m)
                    <a href="{{ route($m['route']) }}" wire:navigate
                       @class(['rounded-xl px-3 py-2 text-sm font-bold transition',
                           'bg-brand-100 text-brand-700' => request()->routeIs($m['aktif']),
                           'text-slate-600 hover:bg-orange-50 hover:text-slate-800' => ! request()->routeIs($m['aktif'])])>
                        {{ $m['label'] }}
                    </a>
                @endforeach
            </nav>

            <div x-data="{ buka: false }" class="relative">
                <button x-on:click="buka = !buka" class="flex items-center gap-2 rounded-xl px-2 py-1.5 hover:bg-orange-50">
                    <span class="grid h-9 w-9 place-items-center rounded-full bg-toska-100 text-sm font-extrabold text-toska-800">
                        {{ str(auth()->user()->name)->substr(0, 1)->upper() }}
                    </span>
                    <span class="hidden max-w-36 truncate text-sm font-bold text-slate-700 lg:block">{{ auth()->user()->name }}</span>
                    <x-heroicon-m-chevron-down class="h-4 w-4 text-slate-400" />
                </button>
                <div x-cloak x-show="buka" x-on:click.outside="buka = false" x-transition
                     class="absolute right-0 mt-2 w-60 overflow-hidden rounded-2xl border border-slate-100 bg-white py-1 shadow-xl">
                    <div class="border-b border-slate-100 px-4 py-3">
                        <p class="truncate text-sm font-extrabold">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-500">{{ auth()->user()->isGuru() ? (\App\Models\User::JENIS_GURU[auth()->user()->jenis_guru] ?? 'Guru') : \App\Models\User::PERAN[auth()->user()->peran] }}</p>
                    </div>
                    <a href="{{ route('guru.panduan') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold hover:bg-orange-50">
                        <x-heroicon-o-book-open class="h-5 w-5 text-slate-400" /> Panduan / Manual Book
                    </a>
                    <a href="{{ route('guru.akun') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold hover:bg-orange-50">
                        <x-heroicon-o-key class="h-5 w-5 text-slate-400" /> Ubah Kata Sandi
                    </a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ url('/admin') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold hover:bg-orange-50">
                            <x-heroicon-o-cog-6-tooth class="h-5 w-5 text-slate-400" /> Halaman Admin
                        </a>
                    @endif
                    <form method="POST" action="{{ route('keluar') }}">
                        @csrf
                        <button class="flex w-full items-center gap-2 px-4 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50">
                            <x-heroicon-o-arrow-right-on-rectangle class="h-5 w-5" /> Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-6">
        {{ $slot }}
    </main>

    {{-- Navigasi bawah untuk HP --}}
    <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-orange-100 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur md:hidden">
        <div class="grid" style="grid-template-columns: repeat({{ count($menu) }}, minmax(0, 1fr))">
            @foreach ($menu as $m)
                <a href="{{ route($m['route']) }}" wire:navigate
                   @class(['flex flex-col items-center gap-0.5 py-2 text-[11px] font-bold',
                       'text-brand-600' => request()->routeIs($m['aktif']),
                       'text-slate-500' => ! request()->routeIs($m['aktif'])])>
                    <x-dynamic-component :component="$m['ikon']" class="h-6 w-6" />
                    {{ $m['pendek'] ?? $m['label'] }}
                </a>
            @endforeach
        </div>
    </nav>

    @livewireScripts
</body>
</html>
