<x-layouts.publik>
    {{-- Pembuka --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-orange-100 via-krem to-teal-50">
        <div class="pointer-events-none absolute -right-20 -top-20 h-72 w-72 rounded-full bg-yellow-200/60 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-16 h-72 w-72 rounded-full bg-teal-200/50 blur-3xl"></div>
        <div class="relative mx-auto grid max-w-6xl items-center gap-10 px-4 py-14 md:grid-cols-2 md:py-20">
            <div>
                <span class="lencana bg-white px-3 py-1 text-sm text-brand-700 shadow-sm">📚 Program Inovasi Literasi Sekolah</span>
                <h1 class="mt-4 text-4xl font-extrabold leading-tight tracking-tight text-slate-800 sm:text-5xl">
                    <span class="text-brand-500">TE</span>ras <span class="text-brand-500">BA</span>ca <span class="text-brand-500">K</span>elas<br>
                    <span class="text-toska-600">A</span>sah Karakter dan litera<span class="text-toska-600">SI</span>
                </h1>
                <p class="mt-4 max-w-lg text-lg text-slate-600">Membaca 15 menit setiap hari, berkarya dari bacaan, dan tumbuh berkarakter bersama {{ \App\Models\Pengaturan::ambil('nama_sekolah') }}.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('galeri') }}" class="tombol-utama px-6 py-3 text-base">Lihat Galeri Kegiatan</a>
                    @if ($tentang)
                        <a href="{{ route('halaman', $tentang->slug) }}" class="tombol-kedua px-6 py-3 text-base">{{ $tentang->judul }}</a>
                    @endif
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                @php
                    $kartuStat = [
                        ['📖', number_format($statistik['membaca'], 0, ',', '.'), 'kali murid membaca bulan ini', 'bg-white'],
                        ['🗓️', number_format($statistik['kegiatan'], 0, ',', '.'), 'sesi Teras Baca kelas bulan ini', 'bg-brand-500 text-white'],
                        ['🎨', number_format($statistik['karya'], 0, ',', '.'), 'karya literasi bulan ini', 'bg-toska-600 text-white'],
                        ['🧒', number_format($statistik['murid'], 0, ',', '.'), 'murid ikut membaca', 'bg-white'],
                    ];
                @endphp
                @foreach ($kartuStat as $i => [$ikon, $angka, $teks, $warna])
                    <div @class(["rounded-3xl p-5 shadow-sm $warna", 'translate-y-6' => $i % 2 === 1])>
                        <div class="text-3xl">{{ $ikon }}</div>
                        <div class="mt-2 text-3xl font-extrabold">{{ $angka }}</div>
                        <div class="text-sm font-semibold opacity-80">{{ $teks }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Rangkaian kegiatan --}}
    <section class="mx-auto max-w-6xl px-4 py-14">
        <h2 class="text-center text-3xl font-extrabold">Satu rangkaian setiap hari</h2>
        <p class="mx-auto mt-2 max-w-2xl text-center text-slate-600">Setiap sesi Teras Baca memadukan kebiasaan membaca, kemampuan literasi, dan pembinaan karakter.</p>
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['📖', 'Membaca', 'Murid membaca 15 menit dengan fokus dan mencatatnya di jurnal literasi.', 'bg-orange-100'],
                ['✍️', 'Memahami & Merangkum', 'Murid menemukan ide pokok dan menuliskannya dengan bahasa sendiri.', 'bg-yellow-100'],
                ['🗣️', 'Menyampaikan', 'Murid menceritakan kembali isi bacaan dengan percaya diri.', 'bg-teal-100'],
                ['🌱', 'Asah Karakter', 'Guru membina karakter murid dan memantau perkembangannya setiap minggu.', 'bg-violet-100'],
            ] as [$ikon, $judul, $teks, $warna])
                <div class="kartu p-5">
                    <div class="grid h-12 w-12 place-items-center rounded-2xl {{ $warna }} text-2xl">{{ $ikon }}</div>
                    <h3 class="mt-3 text-lg font-extrabold">{{ $judul }}</h3>
                    <p class="mt-1 text-sm text-slate-600">{{ $teks }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Galeri --}}
    @if ($galeri->isNotEmpty())
        <section class="bg-white py-14">
            <div class="mx-auto max-w-6xl px-4">
                <div class="flex items-end justify-between gap-3">
                    <h2 class="text-3xl font-extrabold">Galeri Kegiatan</h2>
                    <a href="{{ route('galeri') }}" class="tautan">Lihat semua →</a>
                </div>
                <div class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-4">
                    @foreach ($galeri as $f)
                        <img src="{{ $f->url() }}" alt="{{ $f->keterangan ?? 'Dokumentasi TEBAK AKSI' }}" loading="lazy" class="aspect-square w-full rounded-2xl object-cover">
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Berita --}}
    @if ($berita->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-14">
            <div class="flex items-end justify-between gap-3">
                <h2 class="text-3xl font-extrabold">Kabar Terbaru</h2>
                <a href="{{ route('berita') }}" class="tautan">Semua berita →</a>
            </div>
            <div class="mt-6 grid gap-4 md:grid-cols-3">
                @foreach ($berita as $b)
                    @include('publik._kartu-berita', ['b' => $b])
                @endforeach
            </div>
        </section>
    @endif

    {{-- Ajakan pengaduan --}}
    <section class="mx-auto max-w-6xl px-4">
        <div class="flex flex-col items-start justify-between gap-4 rounded-3xl bg-gradient-to-r from-toska-600 to-teal-500 p-8 text-white sm:flex-row sm:items-center">
            <div>
                <h2 class="text-2xl font-extrabold">Punya saran atau pertanyaan?</h2>
                <p class="text-teal-50">Sampaikan kepada kami melalui Layanan Pengaduan TEBAK AKSI.</p>
            </div>
            <a href="{{ route('pengaduan') }}" class="tombol bg-white px-6 py-3 text-base text-toska-700 hover:bg-teal-50">Kirim Pesan</a>
        </div>
    </section>
</x-layouts.publik>
