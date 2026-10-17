@php
    $peran = auth()->user()->peran;
    $menu = [
        ['route' => 'guru.beranda', 'aktif' => 'guru.beranda', 'label' => 'Beranda', 'ikon' => 'heroicon-o-home'],
        ['route' => 'guru.kegiatan', 'aktif' => 'guru.kegiatan*', 'label' => 'Kegiatan Harian', 'pendek' => 'Harian', 'ikon' => 'heroicon-o-check-circle'],
        ['route' => 'guru.pembinaan', 'aktif' => 'guru.pembinaan*', 'label' => 'Pembinaan', 'ikon' => 'heroicon-o-heart'],
        ['route' => 'guru.karya', 'aktif' => 'guru.karya*', 'label' => 'Karya Literasi', 'pendek' => 'Karya', 'ikon' => 'heroicon-o-document-text'],
        ['route' => 'guru.rekap', 'aktif' => 'guru.rekap*', 'label' => 'Rekap', 'ikon' => 'heroicon-o-table-cells'],
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
    <meta name="theme-color" content="#fffaf3">
    <title>{{ isset($judul) ? $judul.' · ' : '' }}TEBAK AKSI</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen pb-24 md:pb-10">
    <x-notifikasi />

    <header class="sticky top-0 z-30 border-b border-garis bg-white">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4">
            <a href="{{ $beranda }}" wire:navigate><x-logo kecil /></a>

            <nav class="hidden h-full items-stretch gap-1 md:flex" aria-label="Menu utama">
                @foreach ($menu as $m)
                    <a href="{{ route($m['route']) }}" wire:navigate
                       @if (request()->routeIs($m['aktif'])) aria-current="page" @endif
                       @class(['flex items-center border-b-2 px-3 text-sm font-bold transition-colors',
                           'border-brand-700 text-brand-800' => request()->routeIs($m['aktif']),
                           'border-transparent text-slate-600 hover:text-slate-900' => ! request()->routeIs($m['aktif'])])>
                        {{ $m['label'] }}
                    </a>
                @endforeach
            </nav>

            <div x-data="{ buka: false }" class="relative">
                <button x-on:click="buka = !buka" class="flex min-h-11 items-center gap-2 rounded-lg px-2 hover:bg-slate-50" aria-label="Menu akun">
                    <span class="grid h-8 w-8 place-items-center rounded-full bg-slate-100 text-sm font-extrabold text-slate-700">
                        {{ str(auth()->user()->name)->substr(0, 1)->upper() }}
                    </span>
                    <span class="hidden max-w-40 truncate text-sm font-bold text-slate-700 lg:block">{{ auth()->user()->name }}</span>
                    <x-heroicon-m-chevron-down class="h-4 w-4 text-slate-500" />
                </button>
                <div x-cloak x-show="buka" x-on:click.outside="buka = false" x-on:keydown.escape.window="buka = false"
                     class="absolute right-0 mt-2 w-64 overflow-hidden rounded-xl border border-garis bg-white py-1 shadow-lg">
                    <div class="border-b border-garis px-4 py-3">
                        <p class="truncate text-sm font-extrabold">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-600">{{ auth()->user()->isGuru() ? (\App\Models\User::JENIS_GURU[auth()->user()->jenis_guru] ?? 'Guru') : \App\Models\User::PERAN[auth()->user()->peran] }}</p>
                    </div>
                    <a href="{{ route('guru.panduan') }}" class="flex min-h-11 items-center gap-2 px-4 text-sm font-semibold hover:bg-slate-50">
                        <x-heroicon-o-book-open class="h-5 w-5 text-slate-500" /> Panduan penggunaan
                    </a>
                    <a href="{{ route('guru.akun') }}" wire:navigate class="flex min-h-11 items-center gap-2 px-4 text-sm font-semibold hover:bg-slate-50">
                        <x-heroicon-o-key class="h-5 w-5 text-slate-500" /> Ubah kata sandi
                    </a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ url('/admin') }}" class="flex min-h-11 items-center gap-2 px-4 text-sm font-semibold hover:bg-slate-50">
                            <x-heroicon-o-cog-6-tooth class="h-5 w-5 text-slate-500" /> Halaman admin
                        </a>
                    @endif
                    <form method="POST" action="{{ route('keluar') }}" class="border-t border-garis">
                        @csrf
                        <button class="flex min-h-11 w-full items-center gap-2 px-4 text-sm font-semibold text-rose-700 hover:bg-rose-50">
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
    <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-garis bg-white pb-[env(safe-area-inset-bottom)] md:hidden" aria-label="Menu utama">
        <div class="grid" style="grid-template-columns: repeat({{ count($menu) }}, minmax(0, 1fr))">
            @foreach ($menu as $m)
                <a href="{{ route($m['route']) }}" wire:navigate
                   @if (request()->routeIs($m['aktif'])) aria-current="page" @endif
                   @class(['flex min-h-14 flex-col items-center justify-center gap-0.5 text-[11px] font-bold',
                       'text-brand-800' => request()->routeIs($m['aktif']),
                       'text-slate-600' => ! request()->routeIs($m['aktif'])])>
                    <x-dynamic-component :component="$m['ikon']" class="h-6 w-6" />
                    {{ $m['pendek'] ?? $m['label'] }}
                </a>
            @endforeach
        </div>
    </nav>

    @livewireScripts
</body>
</html>
