@use('App\Services\Rekap')
@use('App\Models\Pengaturan')

@php
    $akronim = [
        ['TE', 'ras'],
        ['BA', 'ca'],
        ['K', 'elas'],
        ['A', 'sah Karakter'],
        ['dan litera', 'SI', true],
    ];
    $angka = [
        [$statistik['membaca'], 'kali murid hadir membaca'],
        [$statistik['kegiatan'], 'sesi Teras Baca di semua kelas'],
        [$statistik['karya'], 'karya literasi dibuat murid dan guru'],
        [$statistik['murid'], 'murid terdaftar dalam program'],
    ];
    $langkah = [
        ['Membaca 15 menit', 'Murid memilih buku sendiri dan membaca dengan tenang, lalu mencatatnya di jurnal literasi.'],
        ['Memahami dan merangkum', 'Murid menemukan ide pokok bacaan dan menuliskannya dengan bahasa sendiri.'],
        ['Menyampaikan', 'Murid menceritakan kembali isi bacaan kepada teman atau guru.'],
        ['Membina karakter', 'Bila ada pelanggaran, guru membina murid dan memantau perubahannya setiap minggu selama empat minggu.'],
    ];
@endphp

<x-layouts.publik>
    {{-- Pembuka --}}
    <section class="border-b border-garis bg-white">
        <div class="mx-auto grid max-w-6xl items-center gap-10 px-4 py-12 lg:grid-cols-[1.1fr_1fr] lg:gap-16 lg:py-20">
            <div>
                <p class="text-sm font-bold text-brand-800">{{ Pengaturan::ambil('nama_sekolah') }}</p>
                <h1 class="mt-3 text-balance text-4xl font-extrabold leading-[1.1] tracking-tight text-slate-900 sm:text-5xl">
                    Lima belas menit membaca, setiap hari, di teras kelas.
                </h1>
                <p class="mt-5 max-w-xl text-lg leading-relaxed text-slate-700">
                    TEBAK AKSI mengajak murid membaca, merangkum, dan menceritakan kembali bacaannya, sementara guru membina karakter mereka. Semuanya dicatat setiap hari, sehingga kemajuan setiap kelas terlihat jelas.
                </p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('galeri') }}" class="tombol-utama px-5 text-base">Lihat kegiatan murid</a>
                    @if ($tentang)
                        <a href="{{ route('halaman', $tentang->slug) }}" class="tombol-kedua px-5 text-base">{{ $tentang->judul }}</a>
                    @endif
                </div>
            </div>

            {{-- Motif identitas: arti nama program ditulis di halaman bergaris seperti buku jurnal literasi --}}
            <figure class="halaman-jurnal relative rounded-xl border border-garis py-6 pl-14 pr-6 sm:pl-16" aria-label="Arti nama TEBAK AKSI">
                <figcaption class="text-sm font-bold leading-[3rem] text-slate-600">Arti nama TEBAK AKSI</figcaption>
                <ul>
                    @foreach ($akronim as $baris)
                        <li class="text-[1.65rem] leading-[3rem] text-slate-700 sm:text-3xl sm:leading-[3rem]">
                            @if ($baris[2] ?? false)
                                <span class="font-semibold">{{ $baris[0] }}</span><span class="font-extrabold text-brand-700">{{ $baris[1] }}</span>
                            @else
                                <span class="font-extrabold text-brand-700">{{ $baris[0] }}</span><span class="font-semibold">{{ $baris[1] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </figure>
        </div>
    </section>

    {{-- Angka bulan ini --}}
    <section class="border-b border-garis">
        <div class="mx-auto max-w-6xl px-4 py-10">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 class="text-lg font-extrabold">{{ Rekap::labelBulan(today()) }} dalam angka</h2>
                <p class="text-sm text-slate-600">Dihitung dari catatan harian guru. Nama murid tidak ditampilkan.</p>
            </div>
            <dl class="mt-6 grid grid-cols-2 gap-y-8 lg:grid-cols-4 lg:divide-x lg:divide-garis">
                @foreach ($angka as $i => [$nilai, $label])
                    <div @class(['lg:px-8' => $i > 0, 'lg:pr-8' => $i === 0])>
                        <dt class="sr-only">{{ $label }}</dt>
                        <dd class="text-4xl font-extrabold tabular-nums tracking-tight text-slate-900 sm:text-5xl">{{ number_format($nilai, 0, ',', '.') }}</dd>
                        <dd class="mt-1 text-sm font-semibold text-slate-700" aria-hidden="true">{{ $label }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- Alur satu sesi --}}
    <section class="bg-white">
        <div class="mx-auto max-w-6xl px-4 py-14">
            <h2 class="text-2xl font-extrabold sm:text-3xl">Yang terjadi di setiap sesi Teras Baca</h2>
            <ol class="relative mt-10 grid gap-8 md:grid-cols-4 md:gap-6">
                {{-- Garis penghubung antar langkah --}}
                <span class="absolute left-5 top-5 hidden h-0.5 w-[calc(75%+1.125rem)] bg-brand-200 md:block" aria-hidden="true"></span>
                <span class="absolute bottom-5 left-5 top-5 w-0.5 bg-brand-200 md:hidden" aria-hidden="true"></span>
                @foreach ($langkah as $i => [$judul, $teks])
                    <li class="relative flex gap-4 md:block">
                        <span class="relative z-10 grid h-10 w-10 shrink-0 place-items-center rounded-full border-2 border-brand-700 bg-white text-lg font-extrabold text-brand-800">{{ $i + 1 }}</span>
                        <div class="md:mt-4">
                            <h3 class="text-lg font-extrabold">{{ $judul }}</h3>
                            <p class="mt-1 leading-relaxed text-slate-700">{{ $teks }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    @if ($galeri->isNotEmpty())
        <section class="border-t border-garis">
            <div class="mx-auto max-w-6xl px-4 py-14">
                <div class="flex items-end justify-between gap-3">
                    <h2 class="text-2xl font-extrabold">Galeri kegiatan</h2>
                    <a href="{{ route('galeri') }}" class="tautan">Lihat semua foto</a>
                </div>
                <div class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-4">
                    @foreach ($galeri as $f)
                        <img src="{{ $f->url() }}" alt="{{ $f->keterangan ?? 'Dokumentasi kegiatan TEBAK AKSI' }}" loading="lazy" class="aspect-square w-full rounded-lg object-cover">
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Jalan pintas sesuai pengunjung --}}
    <section class="border-t border-garis">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 py-14 lg:grid-cols-[1.4fr_1fr]">
            <div>
                <h2 class="text-2xl font-extrabold">Untuk orang tua dan masyarakat</h2>
                <ul class="mt-4 divide-y divide-garis border-y border-garis">
                    @foreach ([
                        [route('galeri'), 'Galeri', 'Foto kegiatan Teras Baca dan karya literasi murid.'],
                        [route('berita'), 'Berita', 'Kabar kegiatan literasi di sekolah.'],
                        [route('diseminasi'), 'Diseminasi', 'Dokumentasi berbagi praktik baik dengan sekolah lain.'],
                        [route('pengaduan'), 'Layanan Pengaduan', 'Sampaikan saran, pertanyaan, atau keluhan tentang program.'],
                    ] as [$url, $judul, $teks])
                        <li>
                            <a href="{{ $url }}" class="group flex items-center gap-4 py-4">
                                <span class="flex-1">
                                    <span class="block font-extrabold group-hover:text-brand-800 group-hover:underline">{{ $judul }}</span>
                                    <span class="block text-slate-700">{{ $teks }}</span>
                                </span>
                                <x-heroicon-m-chevron-right class="h-5 w-5 shrink-0 text-slate-500" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="self-start rounded-xl border border-garis bg-white p-6">
                <h2 class="text-xl font-extrabold">Untuk guru</h2>
                <p class="mt-2 leading-relaxed text-slate-700">Catat kegiatan Teras Baca kelas Anda hari ini. Cukup centang setiap murid; rekap bulanan tersusun sendiri.</p>
                <a href="{{ auth()->check() ? route('masuk.arah') : route('login') }}" class="tombol-utama mt-5 w-full text-base">{{ auth()->check() ? 'Buka aplikasi' : 'Masuk' }}</a>
                @guest
                    <p class="mt-3 text-sm text-slate-700">Belum punya akun? <a href="{{ route('daftar') }}" class="tautan">Daftar sebagai guru</a></p>
                @endguest
            </div>
        </div>
    </section>

    @if ($berita->isNotEmpty())
        <section class="border-t border-garis bg-white">
            <div class="mx-auto max-w-6xl px-4 py-14">
                <div class="flex items-end justify-between gap-3">
                    <h2 class="text-2xl font-extrabold">Berita terbaru</h2>
                    <a href="{{ route('berita') }}" class="tautan">Semua berita</a>
                </div>
                <div class="mt-4 divide-y divide-garis border-y border-garis">
                    @foreach ($berita as $b)
                        @include('publik._kartu-berita', ['b' => $b])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.publik>
