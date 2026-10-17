@php
    $halamanMenu = \App\Models\Konten::terbit()->where('tampil_menu', true)->orderBy('urutan')->get(['judul', 'slug']);
    $menu = [
        ['url' => route('beranda'), 'label' => 'Beranda', 'aktif' => request()->routeIs('beranda')],
        ['url' => route('berita'), 'label' => 'Berita', 'aktif' => request()->routeIs('berita*')],
        ['url' => route('galeri'), 'label' => 'Galeri', 'aktif' => request()->routeIs('galeri')],
        ['url' => route('diseminasi'), 'label' => 'Diseminasi', 'aktif' => request()->routeIs('diseminasi*')],
        ['url' => route('manual'), 'label' => 'Manual Book', 'aktif' => request()->routeIs('manual')],
        ['url' => route('pengaduan'), 'label' => 'Pengaduan', 'aktif' => request()->routeIs('pengaduan')],
    ];
    foreach ($halamanMenu as $h) {
        array_splice($menu, 1, 0, [['url' => route('halaman', $h->slug), 'label' => $h->judul, 'aktif' => request()->is('halaman/'.$h->slug)]]);
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#fffaf3">
    <meta name="description" content="TEBAK AKSI: Teras Baca Kelas Asah Karakter dan Literasi, program inovasi literasi {{ \App\Models\Pengaturan::ambil('nama_sekolah') }}.">
    <title>{{ isset($judul) ? $judul.' · ' : '' }}TEBAK AKSI</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="flex min-h-screen flex-col">
    <x-notifikasi />

    <header x-data="{ buka: false }" class="sticky top-0 z-30 border-b border-garis bg-white">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4">
            <a href="{{ route('beranda') }}"><x-logo /></a>

            <nav class="hidden h-full items-stretch gap-1 xl:flex" aria-label="Menu utama">
                @foreach ($menu as $m)
                    <a href="{{ $m['url'] }}" @if ($m['aktif']) aria-current="page" @endif
                       @class(['flex items-center border-b-2 px-3 text-sm font-bold',
                           'border-brand-700 text-brand-800' => $m['aktif'],
                           'border-transparent text-slate-600 hover:text-slate-900' => ! $m['aktif']])>{{ $m['label'] }}</a>
                @endforeach
                <span class="ml-2 flex items-center">
                    <a href="{{ auth()->check() ? route('masuk.arah') : route('login') }}" class="tombol-utama">
                        {{ auth()->check() ? 'Buka aplikasi' : 'Masuk guru' }}
                    </a>
                </span>
            </nav>

            <button x-on:click="buka = !buka" :aria-expanded="buka" class="flex min-h-11 items-center gap-2 rounded-lg px-3 text-sm font-bold text-slate-700 hover:bg-slate-50 xl:hidden">
                <x-heroicon-o-bars-3 class="h-6 w-6" x-show="!buka" />
                <x-heroicon-o-x-mark class="h-6 w-6" x-show="buka" x-cloak />
                Menu
            </button>
        </div>
        <nav x-cloak x-show="buka" class="border-t border-garis bg-white px-4 py-3 xl:hidden" aria-label="Menu utama">
            @foreach ($menu as $m)
                <a href="{{ $m['url'] }}" @class(['flex min-h-11 items-center rounded-lg px-3 font-bold', 'bg-brand-50 text-brand-800' => $m['aktif'], 'text-slate-700' => ! $m['aktif']])>{{ $m['label'] }}</a>
            @endforeach
            <a href="{{ auth()->check() ? route('masuk.arah') : route('login') }}" class="tombol-utama mt-2 w-full">
                {{ auth()->check() ? 'Buka aplikasi' : 'Masuk guru' }}
            </a>
        </nav>
    </header>

    <main class="flex-1">
        {{ $slot }}
    </main>

    <footer class="mt-16 border-t border-garis bg-white">
        <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-8 text-sm text-slate-600 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="font-extrabold text-slate-800">{{ \App\Models\Pengaturan::ambil('nama_sekolah') }}</p>
                @if ($alamat = \App\Models\Pengaturan::ambil('alamat_sekolah'))
                    <p class="mt-1">{{ $alamat }}</p>
                @endif
                @if ($kontak = \App\Models\Pengaturan::ambil('kontak_sekolah'))
                    <p class="mt-1">{{ $kontak }}</p>
                @endif
            </div>
            <div class="sm:text-right">
                <a href="{{ route('pengaduan') }}" class="tautan">Kirim saran atau pengaduan</a>
                <p class="mt-2">© {{ date('Y') }} Program TEBAK AKSI</p>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
