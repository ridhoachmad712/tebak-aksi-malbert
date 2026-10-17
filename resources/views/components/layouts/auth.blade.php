<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f97316">
    <title>{{ $judul ?? 'Masuk' }} · TEBAK AKSI</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-orange-50 via-krem to-teal-50">
    <x-notifikasi />
    <div class="mx-auto flex min-h-screen max-w-md flex-col justify-center px-4 py-10">
        <a href="{{ route('beranda') }}" class="mx-auto mb-8"><x-logo /></a>
        <div class="kartu p-6 sm:p-8">
            {{ $slot }}
        </div>
        <p class="mt-6 text-center text-sm"><a href="{{ route('beranda') }}" class="font-semibold text-slate-500 hover:text-slate-700">← Kembali ke halaman utama</a></p>
    </div>
</body>
</html>
