@use('App\Services\Rekap')
@use('App\Models\Pengaturan')

@php
    // Setiap baris: potongan teks dan penanda huruf pembentuk akronim (TE-BA-K A-K-SI)
    $akronim = [
        [['TE', true], ['ras', false]],
        [['BA', true], ['ca', false]],
        [['K', true], ['elas', false]],
        [['A', true], ['sah ', false], ['K', true], ['arakter', false]],
        [['dan litera', false], ['SI', true]],
    ];
    $angka = [
        [$statistik['membaca'], 'kali murid hadir membaca'],
        [$statistik['kegiatan'], 'sesi Teras Baca di semua kelas'],
        [$statistik['karya'], 'karya literasi dibuat murid dan guru'],
        [$statistik['murid'], 'murid terdaftar dalam program'],
    ];
    $langkah = Pengaturan::daftar('alur_langkah');
    $tombol1 = ['teks' => Pengaturan::ambil('beranda_tombol1_teks'), 'url' => Pengaturan::urlTujuan(Pengaturan::ambil('beranda_tombol1_tujuan'))];
    $tombol2 = ['teks' => Pengaturan::ambil('beranda_tombol2_teks'), 'url' => Pengaturan::urlTujuan(Pengaturan::ambil('beranda_tombol2_tujuan'))];
    $jumlahLangkah = max(1, count($langkah));
@endphp

<x-layouts.publik>
    {{-- Pembuka --}}
    <section class="border-b border-garis bg-white">
        <div class="mx-auto grid max-w-6xl items-center gap-10 px-4 py-12 lg:grid-cols-[1.1fr_1fr] lg:gap-16 lg:py-20">
            <div>
                <p class="text-sm font-bold text-brand-800">{{ Pengaturan::ambil('nama_sekolah') }}</p>
                <h1 class="mt-3 text-balance text-4xl font-extrabold leading-[1.1] tracking-tight text-slate-900 sm:text-5xl">{{ Pengaturan::ambil('beranda_judul') }}</h1>
                <p class="mt-5 max-w-xl text-lg leading-relaxed text-slate-700">{{ Pengaturan::ambil('beranda_paragraf') }}</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    @if ($tombol1['teks'] && $tombol1['url'])
                        <a href="{{ $tombol1['url'] }}" class="tombol-utama px-5 text-base">{{ $tombol1['teks'] }}</a>
                    @endif
                    @if ($tombol2['teks'] && $tombol2['url'])
                        <a href="{{ $tombol2['url'] }}" class="tombol-kedua px-5 text-base">{{ $tombol2['teks'] }}</a>
                    @endif
                </div>
            </div>

            @if ($fotoPembuka)
                <figure>
                    <img src="{{ $fotoPembuka->url() }}" alt="{{ $fotoPembuka->keterangan ?? 'Kegiatan Teras Baca' }}" class="aspect-[4/3] w-full rounded-xl border border-garis object-cover">
                    @if ($fotoPembuka->keterangan)
                        <figcaption class="mt-2 text-sm text-slate-600">{{ $fotoPembuka->keterangan }}</figcaption>
                    @endif
                </figure>
            @else
                {{-- Motif identitas: arti nama program ditulis di halaman bergaris seperti buku jurnal literasi --}}
                <figure class="halaman-jurnal relative rounded-xl border border-garis py-6 pl-14 pr-6 sm:pl-16" aria-label="Arti nama TEBAK AKSI">
                    <figcaption class="text-sm font-bold leading-[3rem] text-slate-600">Arti nama TEBAK AKSI</figcaption>
                    <ul>
                        @foreach ($akronim as $baris)
                            <li class="text-[1.65rem] leading-[3rem] text-slate-700 sm:text-3xl sm:leading-[3rem]">
                                {{-- Ditulis dalam satu baris agar tidak ada spasi di antara potongan kata --}}
                                @foreach ($baris as [$teks, $huruf])<span @class(['font-extrabold text-brand-700' => $huruf, 'font-semibold' => ! $huruf])>{{ $teks }}</span>@endforeach
                            </li>
                        @endforeach
                    </ul>
                </figure>
            @endif
        </div>
    </section>

    @if (Pengaturan::aktif('beranda_tampil_angka'))
        <section class="border-b border-garis">
            <div class="mx-auto max-w-6xl px-4 py-10">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <h2 class="text-lg font-extrabold">{{ $statistik['bulan'] }} dalam angka</h2>
                    <p class="text-sm text-slate-600">
                        Dihitung dari catatan harian guru. Nama murid tidak ditampilkan.
                    </p>
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
    @endif

    @if (Pengaturan::aktif('beranda_tampil_alur') && $langkah)
        <section class="bg-white">
            <div class="mx-auto max-w-6xl px-4 py-14">
                <h2 class="text-2xl font-extrabold sm:text-3xl">{{ Pengaturan::ambil('alur_judul') }}</h2>
                {{-- Jumlah kolom mengikuti jumlah langkah yang diatur admin --}}
                <ol class="relative mt-10 grid gap-8 md:grid-cols-[repeat(var(--jumlah),minmax(0,1fr))] md:gap-6" style="--jumlah: {{ $jumlahLangkah }}">
                    @if ($jumlahLangkah > 1)
                        {{-- Garis penghubung dari pusat lingkaran pertama ke pusat lingkaran terakhir --}}
                        <span class="absolute left-5 top-5 hidden h-0.5 bg-brand-200 md:block" style="width: calc((100% - (var(--jumlah) - 1) * 1.5rem) / var(--jumlah) * (var(--jumlah) - 1) + (var(--jumlah) - 1) * 1.5rem)" aria-hidden="true"></span>
                        <span class="absolute bottom-5 left-5 top-5 w-0.5 bg-brand-200 md:hidden" aria-hidden="true"></span>
                    @endif
                    @foreach ($langkah as $i => $l)
                        <li class="relative flex gap-4 md:block">
                            <span class="relative z-10 grid h-10 w-10 shrink-0 place-items-center rounded-full border-2 border-brand-700 bg-white text-lg font-extrabold text-brand-800">{{ $i + 1 }}</span>
                            <div class="md:mt-4">
                                <h3 class="text-lg font-extrabold">{{ $l['judul'] ?? '' }}</h3>
                                <p class="mt-1 leading-relaxed text-slate-700">{{ $l['keterangan'] ?? '' }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    @if (Pengaturan::aktif('beranda_tampil_galeri') && $galeri->isNotEmpty())
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

    @if ($karyaTerbaru->isNotEmpty())
        <section class="border-t border-garis">
            <div class="mx-auto max-w-6xl px-4 py-14">
                <div class="flex items-end justify-between gap-3">
                    <h2 class="text-2xl font-extrabold">Karya literasi terbaru</h2>
                    <a href="{{ route('karya.publik') }}" class="tautan">Lihat semua karya</a>
                </div>
                <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-4">
                    @foreach ($karyaTerbaru as $k)
                        <a href="{{ route('karya.publik', ['kelas' => $k->kelas_id]) }}" class="group overflow-hidden rounded-xl border border-garis bg-white">
                            <img src="{{ $k->foto->first()->url() }}" alt="Foto karya {{ $k->judul }}" loading="lazy" class="aspect-square w-full object-cover">
                            <span class="block p-3">
                                <span class="block font-extrabold leading-snug group-hover:underline">{{ $k->judul }}</span>
                                <span class="block text-sm text-slate-700">{{ $k->murid->pluck('nama')->join(', ') }} · Kelas {{ $k->kelas->nama }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if (Pengaturan::aktif('beranda_tampil_jalan_pintas'))
        <section class="border-t border-garis">
            <div class="mx-auto grid max-w-6xl gap-10 px-4 py-14 lg:grid-cols-[1.4fr_1fr]">
                <div>
                    <h2 class="text-2xl font-extrabold">Untuk orang tua dan masyarakat</h2>
                    <ul class="mt-4 divide-y divide-garis border-y border-garis">
                        @foreach ([
                            [route('karya.publik'), 'Karya Literasi', 'Karya murid dari kegiatan Teras Baca, per bulan dan kelas.'],
                            [route('galeri'), 'Galeri', 'Foto kegiatan Teras Baca.'],
                            [route('berita'), 'Berita', 'Kabar kegiatan literasi di sekolah.'],
                            [route('diseminasi'), 'Diseminasi', 'Dokumentasi berbagi praktik baik dengan sekolah lain.'],
                            [route('pengaduan'), 'Layanan Pengaduan', 'Sampaikan saran, pertanyaan, atau keluhan tentang program.'],
                        ] as [$url, $judulTautan, $teks])
                            <li>
                                <a href="{{ $url }}" class="group flex items-center gap-4 py-4">
                                    <span class="flex-1">
                                        <span class="block font-extrabold group-hover:text-brand-800 group-hover:underline">{{ $judulTautan }}</span>
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
    @endif

    @if (Pengaturan::aktif('beranda_tampil_berita') && $berita->isNotEmpty())
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
