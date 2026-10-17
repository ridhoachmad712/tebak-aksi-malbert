<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#fffaf3">
    <title>{{ $judul ?? 'Masuk' }} · TEBAK AKSI</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-ikon-situs />
</head>
<body class="min-h-screen">
    <x-notifikasi />
    <div class="mx-auto flex min-h-screen max-w-md flex-col justify-center px-4 py-10">
        <a href="{{ route('beranda') }}" class="mb-6"><x-logo /></a>
        <div class="kartu p-6 sm:p-8">
            {{ $slot }}
        </div>
        <p class="mt-6 text-sm"><a href="{{ route('beranda') }}" class="font-semibold text-slate-600 hover:text-slate-900">Kembali ke halaman utama</a></p>
    </div>
</body>
</html>
