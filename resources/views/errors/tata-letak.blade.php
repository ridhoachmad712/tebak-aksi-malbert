{{-- Halaman galat sengaja tidak membaca database, agar tetap tampil walau server bermasalah --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $judul }} · TEBAK AKSI</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #fffaf3; color: #1e293b; font-family: 'Nunito', ui-sans-serif, system-ui, sans-serif; }
        main { max-width: 32rem; padding: 2rem 1.25rem; }
        .kode { font-size: .875rem; font-weight: 700; color: #475569; }
        h1 { margin: .25rem 0 0; font-size: 1.75rem; font-weight: 800; line-height: 1.2; }
        p { margin: .75rem 0 0; line-height: 1.6; color: #334155; }
        .aksi { margin-top: 1.5rem; display: flex; flex-wrap: wrap; gap: .5rem; }
        a, button { display: inline-flex; align-items: center; min-height: 2.75rem; padding: 0 1rem; border-radius: .5rem; font: inherit; font-weight: 700; font-size: .9rem; text-decoration: none; cursor: pointer; }
        .utama { background: #c2410c; color: #fff; border: 0; }
        .kedua { background: #fff; color: #334155; border: 1px solid #ece3d6; }
    </style>
</head>
<body>
    <main>
        <p class="kode">TEBAK AKSI · Kode {{ $kode }}</p>
        <h1>{{ $judul }}</h1>
        <p>{{ $pesan }}</p>
        <div class="aksi">
            {{ $aksi ?? '' }}
            <a href="{{ url('/') }}" class="kedua">Ke halaman utama</a>
        </div>
    </main>
</body>
</html>
