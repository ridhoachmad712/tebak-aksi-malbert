@use('App\Models\Pengaturan')

@php
    // Menu disusun admin di Tampilan Situs; tujuan yang sudah tidak ada dilewati agar tidak ada tautan mati
    $sekarang = request()->url();
    $menu = collect(Pengaturan::daftar('menu_publik'))
        ->filter(fn ($m) => filter_var($m['tampil'] ?? true, FILTER_VALIDATE_BOOLEAN))
        ->map(fn ($m) => ['url' => Pengaturan::urlTujuan($m['tujuan'] ?? null), 'label' => $m['label'] ?? ''])
        ->filter(fn ($m) => $m['url'] && $m['label'] !== '')
        ->map(fn ($m) => $m + ['aktif' => $sekarang === $m['url'] || ($m['url'] !== route('beranda') && str_starts_with($sekarang, $m['url']))])
        ->values();

    $wa = preg_replace('/\D/', '', (string) Pengaturan::ambil('medsos_whatsapp'));
    $medsos = array_filter([
        'Instagram' => Pengaturan::ambil('medsos_instagram'),
        'Facebook' => Pengaturan::ambil('medsos_facebook'),
        'YouTube' => Pengaturan::ambil('medsos_youtube'),
        'WhatsApp' => $wa ? 'https://wa.me/'.(str_starts_with($wa, '0') ? '62'.substr($wa, 1) : $wa) : null,
    ]);
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
    <x-ikon-situs />
    @livewireStyles
</head>
<body class="flex min-h-screen flex-col">
    <x-notifikasi />
    <x-pengumuman sasaran="publik" />

    <header x-data="{ buka: false }" class="sticky top-0 z-30 border-b border-garis bg-white">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4">
            <a href="{{ route('beranda') }}" class="shrink-0"><x-logo kecil /></a>

            <nav class="hidden h-full items-stretch gap-1 xl:flex" aria-label="Menu utama">
                @foreach ($menu as $m)
                    <a href="{{ $m['url'] }}" @if ($m['aktif']) aria-current="page" @endif
                       @class(['flex items-center whitespace-nowrap border-b-2 px-3 text-sm font-bold',
                           'border-brand-700 text-brand-800' => $m['aktif'],
                           'border-transparent text-slate-600 hover:text-slate-900' => ! $m['aktif']])>{{ $m['label'] }}</a>
                @endforeach
                <span class="ml-3 flex items-center">
                    <a href="{{ auth()->check() ? route('masuk.arah') : route('login') }}" class="tombol-utama whitespace-nowrap">
                        {{ auth()->check() ? 'Buka aplikasi' : 'Masuk' }}
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
                {{ auth()->check() ? 'Buka aplikasi' : 'Masuk' }}
            </a>
        </nav>
    </header>

    <main class="flex-1">
        {{ $slot }}
    </main>

    <footer class="mt-16 border-t border-garis bg-white">
        <div class="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-8 text-sm text-slate-600 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="font-extrabold text-slate-800">{{ Pengaturan::ambil('nama_sekolah') }}</p>
                @if ($alamat = Pengaturan::ambil('alamat_sekolah'))
                    <p class="mt-1">{{ $alamat }}</p>
                @endif
                @if ($kontak = Pengaturan::ambil('kontak_sekolah'))
                    <p class="mt-1">{{ $kontak }}</p>
                @endif
                @if ($medsos)
                    <ul class="mt-3 flex flex-wrap gap-x-4 gap-y-1">
                        @foreach ($medsos as $nama => $url)
                            <li><a href="{{ $url }}" target="_blank" rel="noopener" class="tautan">{{ $nama }}</a></li>
                        @endforeach
                    </ul>
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
