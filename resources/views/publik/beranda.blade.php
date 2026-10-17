@use('App\Services\Rekap')

<x-layouts.publik>
    <section class="border-b border-garis bg-white">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 md:grid-cols-[1.4fr_1fr] md:py-16">
            <div>
                <h1 class="text-4xl font-extrabold leading-tight tracking-tight text-slate-800 sm:text-5xl">
                    <span class="text-brand-700">TE</span>ras <span class="text-brand-700">BA</span>ca <span class="text-brand-700">K</span>elas
                    <span class="text-brand-700">A</span>sah Karakter dan litera<span class="text-brand-700">SI</span>
                </h1>
                <p class="mt-4 max-w-xl text-lg text-slate-700">Program literasi {{ \App\Models\Pengaturan::ambil('nama_sekolah') }}. Setiap hari murid membaca 15 menit di Teras Baca kelasnya, lalu guru mendampingi dan mencatat perkembangan literasi dan karakter mereka.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('galeri') }}" class="tombol-utama px-5 text-base">Lihat galeri kegiatan</a>
                    @if ($tentang)
                        <a href="{{ route('halaman', $tentang->slug) }}" class="tombol-kedua px-5 text-base">{{ $tentang->judul }}</a>
                    @endif
                </div>
            </div>

            <div class="rounded-xl border border-garis bg-krem p-5">
                <h2 class="font-extrabold">Bulan {{ Rekap::labelBulan(today()) }}</h2>
                <dl class="mt-3 divide-y divide-garis">
                    @foreach ([
                        [$statistik['kegiatan'], 'sesi Teras Baca tercatat di semua kelas'],
                        [$statistik['membaca'], 'kali murid hadir membaca'],
                        [$statistik['karya'], 'karya literasi dibuat'],
                        [$statistik['murid'], 'murid terdaftar dalam program'],
                    ] as [$angka, $label])
                        <div class="flex items-baseline gap-3 py-2.5">
                            <dt class="sr-only">{{ $label }}</dt>
                            <dd class="w-20 shrink-0 text-2xl font-extrabold tabular-nums text-slate-800">{{ number_format($angka, 0, ',', '.') }}</dd>
                            <dd class="text-sm text-slate-700" aria-hidden="true">{{ $label }}</dd>
                        </div>
                    @endforeach
                </dl>
                <p class="mt-2 text-xs text-slate-600">Dihitung dari catatan harian guru. Nama murid tidak ditampilkan.</p>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-12">
        <h2 class="text-2xl font-extrabold">Yang terjadi di setiap sesi</h2>
        <ol class="mt-6 grid gap-x-8 gap-y-6 md:grid-cols-2">
            @foreach ([
                ['Membaca 15 menit', 'Murid memilih buku dan membaca dengan tenang, lalu mencatatnya di jurnal literasi.'],
                ['Memahami dan merangkum', 'Murid menemukan ide pokok bacaan dan menuliskannya dengan bahasa sendiri.'],
                ['Menyampaikan', 'Murid menceritakan kembali isi bacaan kepada teman atau guru.'],
                ['Membina karakter', 'Bila ada pelanggaran, guru membina murid dan memantau perubahannya setiap minggu selama empat minggu.'],
            ] as $i => [$judul, $teks])
                <li class="flex gap-4">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand-50 font-extrabold text-brand-800">{{ $i + 1 }}</span>
                    <div>
                        <h3 class="font-extrabold">{{ $judul }}</h3>
                        <p class="mt-0.5 text-slate-700">{{ $teks }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </section>

    @if ($galeri->isNotEmpty())
        <section class="border-y border-garis bg-white py-12">
            <div class="mx-auto max-w-6xl px-4">
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

    @if ($berita->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-12">
            <div class="flex items-end justify-between gap-3">
                <h2 class="text-2xl font-extrabold">Berita terbaru</h2>
                <a href="{{ route('berita') }}" class="tautan">Semua berita</a>
            </div>
            <div class="mt-4 divide-y divide-garis border-y border-garis">
                @foreach ($berita as $b)
                    @include('publik._kartu-berita', ['b' => $b])
                @endforeach
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-6xl px-4">
        <p class="text-slate-700">Punya saran atau pertanyaan tentang program ini? <a href="{{ route('pengaduan') }}" class="tautan">Sampaikan melalui Layanan Pengaduan</a>.</p>
    </section>
</x-layouts.publik>
