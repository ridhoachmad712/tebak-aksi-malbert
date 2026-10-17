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
    <meta name="theme-color" content="#f97316">
    <meta name="description" content="TEBAK AKSI: Teras Baca Kelas Asah Karakter dan Literasi, program inovasi literasi {{ \App\Models\Pengaturan::ambil('nama_sekolah') }}.">
    <title>{{ isset($judul) ? $judul.' · ' : '' }}TEBAK AKSI</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="flex min-h-screen flex-col">
    <x-notifikasi />

    <header x-data="{ buka: false }" class="sticky top-0 z-30 border-b border-orange-100 bg-white/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4">
            <a href="{{ route('beranda') }}"><x-logo /></a>

            <nav class="hidden items-center gap-1 xl:flex">
                @foreach ($menu as $m)
                    <a href="{{ $m['url'] }}"
                       @class(['rounded-xl px-3 py-2 text-sm font-bold transition',
                           'bg-brand-100 text-brand-700' => $m['aktif'],
                           'text-slate-600 hover:bg-orange-50' => ! $m['aktif']])>{{ $m['label'] }}</a>
                @endforeach
                <a href="{{ auth()->check() ? route('masuk.arah') : route('login') }}" class="tombol-utama ml-2">
                    {{ auth()->check() ? 'Buka Aplikasi' : 'Masuk Guru' }}
                </a>
            </nav>

            <button x-on:click="buka = !buka" class="rounded-xl p-2 hover:bg-orange-50 xl:hidden" aria-label="Menu">
                <x-heroicon-o-bars-3 class="h-7 w-7" x-show="!buka" />
                <x-heroicon-o-x-mark class="h-7 w-7" x-show="buka" x-cloak />
            </button>
        </div>
        <nav x-cloak x-show="buka" x-transition class="border-t border-orange-100 bg-white px-4 py-3 xl:hidden">
            @foreach ($menu as $m)
                <a href="{{ $m['url'] }}" @class(['block rounded-xl px-3 py-2.5 font-bold', 'bg-brand-100 text-brand-700' => $m['aktif'], 'text-slate-700' => ! $m['aktif']])>{{ $m['label'] }}</a>
            @endforeach
            <a href="{{ auth()->check() ? route('masuk.arah') : route('login') }}" class="tombol-utama mt-2 w-full">
                {{ auth()->check() ? 'Buka Aplikasi' : 'Masuk Guru' }}
            </a>
        </nav>
    </header>

    <main class="flex-1">
        {{ $slot }}
    </main>

    <footer class="mt-16 border-t border-orange-100 bg-white">
        <div class="mx-auto grid max-w-6xl gap-6 px-4 py-10 sm:grid-cols-2">
            <div>
                <x-logo />
                <p class="mt-3 max-w-sm text-sm text-slate-500">Program inovasi literasi {{ \App\Models\Pengaturan::ambil('nama_sekolah') }}.</p>
            </div>
            <div class="text-sm text-slate-600 sm:text-right">
                @if ($alamat = \App\Models\Pengaturan::ambil('alamat_sekolah'))
                    <p>{{ $alamat }}</p>
                @endif
                @if ($kontak = \App\Models\Pengaturan::ambil('kontak_sekolah'))
                    <p class="mt-1">{{ $kontak }}</p>
                @endif
                <p class="mt-3"><a href="{{ route('pengaduan') }}" class="tautan">Kirim saran atau pengaduan</a></p>
                <p class="mt-3 text-xs text-slate-400">© {{ date('Y') }} {{ \App\Models\Pengaturan::ambil('nama_sekolah') }}</p>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
