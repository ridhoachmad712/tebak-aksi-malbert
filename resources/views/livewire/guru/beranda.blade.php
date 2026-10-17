@use('App\Services\Rekap')

<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-sm font-semibold text-slate-600">{{ Rekap::tanggalPanjang(today()) }}</p>
            <h1 class="judul-halaman">Halo, {{ str(auth()->user()->name)->before(',') }}</h1>
        </div>
        <x-pilih-kelas :daftar="$this->daftarKelas" />
    </div>

    {{-- Panduan singkat untuk pengguna baru; disembunyikan setelah ditutup, dapat dibuka lagi dari menu akun --}}
    <div x-data="{ tampil: @js(request()->boolean('panduan')) || (() => { try { return localStorage.getItem('panduan-tutup') !== '1' } catch (e) { return true } })() }"
         x-show="tampil" x-cloak class="kartu flex flex-col gap-4 p-5 sm:flex-row sm:items-start">
        <div class="flex-1">
            <h2 class="font-extrabold">Cara memakai TEBAK AKSI</h2>
            <ol class="mt-2 grid gap-2 text-sm text-slate-700 sm:grid-cols-3">
                <li><b>1. Setiap hari kegiatan</b>, buka Kegiatan Harian lalu centang setiap murid. Perubahan tersimpan sendiri.</li>
                <li><b>2. Saat ada pelanggaran</b>, catat di Pembinaan lalu pantau seminggu sekali selama 4 minggu.</li>
                <li><b>3. Saat ada karya</b>, catat di Karya Literasi. Rekap bulanan tersusun otomatis.</li>
            </ol>
        </div>
        <button x-on:click="tampil = false; try { localStorage.setItem('panduan-tutup', '1') } catch (e) {}" class="tombol-kedua shrink-0">Mengerti, tutup</button>
    </div>

    @if (! $kelas)
        <x-belum-ada-kelas />
    @else
        {{-- Titik fokus: kegiatan hari ini --}}
        <section class="kartu p-5 sm:p-6" aria-labelledby="judul-hari-ini">
            <p class="text-sm font-bold text-slate-600">Kegiatan Harian · Kelas {{ $kelas->nama }}</p>
            <div class="mt-1 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    @if (! $hariKegiatan)
                        <h2 id="judul-hari-ini" class="text-xl font-extrabold">Hari ini bukan hari kegiatan</h2>
                        <p class="teks-bantu mt-1">Kegiatan Teras Baca berlangsung Senin sampai Jumat, kecuali hari libur sekolah.</p>
                    @elseif (! $kegiatanHariIni)
                        <h2 id="judul-hari-ini" class="text-xl font-extrabold">Belum dicatat</h2>
                        <p class="teks-bantu mt-1">Centang kehadiran dan kemampuan literasi {{ $jumlahMurid }} murid.</p>
                    @else
                        <h2 id="judul-hari-ini" class="text-xl font-extrabold">Sudah dicatat</h2>
                        <p class="teks-bantu mt-1">{{ $kegiatanHariIni->hadir_count }} dari {{ $jumlahMurid }} murid hadir membaca. Catatan masih bisa dilengkapi.</p>
                    @endif
                </div>
                @if ($hariKegiatan)
                    <a href="{{ route('guru.kegiatan', ['kelas' => $kelas->id]) }}" wire:navigate class="tombol-utama shrink-0 px-6 text-base">
                        {{ $kegiatanHariIni ? 'Lanjutkan catatan hari ini' : 'Mulai catatan hari ini' }}
                        <x-heroicon-m-arrow-right class="h-5 w-5" />
                    </a>
                @elseif ($hariTerakhir)
                    <a href="{{ route('guru.kegiatan', ['kelas' => $kelas->id, 'tanggal' => $hariTerakhir->toDateString()]) }}" wire:navigate class="tombol-kedua shrink-0">
                        Lihat catatan {{ Rekap::namaHari($hariTerakhir) }}, {{ $hariTerakhir->day }} {{ Rekap::BULAN[$hariTerakhir->month] }}
                    </a>
                @endif
            </div>
            <div class="mt-5 flex flex-wrap gap-2 border-t border-garis pt-4">
                <a href="{{ route('guru.pembinaan.baru') }}" wire:navigate class="tombol-kedua"><x-heroicon-o-heart class="h-5 w-5 text-slate-500" /> Catat pelanggaran</a>
                <a href="{{ route('guru.karya.baru') }}" wire:navigate class="tombol-kedua"><x-heroicon-o-document-text class="h-5 w-5 text-slate-500" /> Tambah karya</a>
                @if ($hariTerakhir && $hariKegiatan)
                    <a href="{{ route('guru.kegiatan', ['kelas' => $kelas->id, 'tanggal' => $hariTerakhir->toDateString()]) }}" wire:navigate class="tombol-kedua"><x-heroicon-o-calendar-days class="h-5 w-5 text-slate-500" /> Catatan {{ Rekap::namaHari($hariTerakhir) }}</a>
                @endif
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="lg:col-span-2" aria-labelledby="judul-perhatian">
                <h2 id="judul-perhatian" class="mb-3 text-lg font-extrabold">Perlu ditindaklanjuti</h2>
                @php $adaTugas = $perluDipantau->isNotEmpty() || $siapDitutup->isNotEmpty() || $belumDicatat->isNotEmpty(); @endphp

                @if ($adaTugas)
                    <ul class="kartu divide-y divide-garis">
                        @foreach ($perluDipantau as $k)
                            @php $belum = $k->mingguBelumDipantau(); @endphp
                            {{-- Pantau langsung dari Beranda: sekali ketuk tersimpan untuk minggu paling awal yang belum diisi --}}
                            <li wire:key="pantau-{{ $k->id }}-{{ $belum[0] }}" class="px-4 py-3">
                                <div class="flex items-start gap-3">
                                    <x-heroicon-o-clipboard-document-check class="mt-0.5 h-6 w-6 shrink-0 text-amber-700" />
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('guru.pembinaan.detail', $k) }}" wire:navigate class="block font-bold hover:underline">Pantau {{ $k->murid->nama }} <span class="font-semibold text-slate-600">· Kelas {{ $k->kelas->nama }}</span></a>
                                        <p class="truncate text-sm text-slate-600">{{ $k->namaPelanggaran() }} · minggu ke-{{ implode(', ', $belum) }} belum diisi</p>
                                        <p class="mt-2 text-xs font-bold text-slate-700">Perkembangan minggu ke-{{ $belum[0] }}:</p>
                                        <div class="mt-1 grid grid-cols-2 gap-1.5 sm:grid-cols-4">
                                            @foreach (\App\Models\KasusPembinaan::PERKEMBANGAN as $nilai => $info)
                                                <button type="button" wire:click="pantau({{ $k->id }}, '{{ $nilai }}')" wire:loading.attr="disabled"
                                                    class="min-h-10 rounded-lg border border-garis bg-white px-2 text-xs font-bold text-slate-800 hover:border-slate-500">{{ $info['label'] }}</button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                        {{-- Murid yang siap ditetapkan status akhirnya juga diringkas menjadi satu butir --}}
                        @if ($siapDitutup->isNotEmpty())
                            <li class="px-4 py-3">
                                <div class="flex items-start gap-3">
                                    <x-heroicon-o-flag class="mt-0.5 h-6 w-6 shrink-0 text-toska-700" />
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold">{{ $siapDitutup->count() }} murid siap ditetapkan status akhirnya</p>
                                        <p class="text-sm text-slate-600">Pemantauan 4 minggu sudah lengkap. Ketuk nama untuk menetapkan.</p>
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            @foreach ($siapDitutup->take(6) as $k)
                                                <a href="{{ route('guru.pembinaan.detail', $k) }}" wire:navigate
                                                    class="inline-flex min-h-10 items-center rounded-lg border border-garis bg-white px-3 text-sm font-bold text-slate-800 hover:border-slate-500">{{ $k->murid->nama }}</a>
                                            @endforeach
                                            @if ($siapDitutup->count() > 6)
                                                <a href="{{ route('guru.pembinaan') }}" wire:navigate class="inline-flex min-h-10 items-center px-2 text-sm font-bold text-slate-700 hover:underline">dan {{ $siapDitutup->count() - 6 }} lainnya</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endif
                        {{-- Hari yang belum dicatat diringkas menjadi satu butir, berapa pun batas ubah yang diatur admin --}}
                        @if ($belumDicatat->isNotEmpty())
                            @php $terbaru = $belumDicatat->reverse()->values(); @endphp
                            <li class="px-4 py-3">
                                <div class="flex items-start gap-3">
                                    <x-heroicon-o-calendar class="mt-0.5 h-6 w-6 shrink-0 text-rose-700" />
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold">{{ $belumDicatat->count() === 1 ? '1 hari kegiatan belum dicatat' : $belumDicatat->count().' hari kegiatan belum dicatat' }}</p>
                                        <p class="text-sm text-slate-600">
                                            Kelas {{ $kelas->nama }}{{ $belumDicatat->count() > 1 ? ', sejak '.Rekap::tanggalPanjang($belumDicatat->first()) : '' }}. Mulai dari yang terbaru selagi masih ingat.
                                        </p>
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            @foreach ($terbaru->take(3) as $tgl)
                                                <a href="{{ route('guru.kegiatan', ['kelas' => $kelas->id, 'tanggal' => $tgl->toDateString()]) }}" wire:navigate
                                                    class="inline-flex min-h-10 items-center rounded-lg border border-garis bg-white px-3 text-sm font-bold text-slate-800 hover:border-slate-500">
                                                    {{ Rekap::namaHari($tgl) }}, {{ $tgl->day }} {{ mb_substr(Rekap::BULAN[$tgl->month], 0, 3) }}
                                                </a>
                                            @endforeach
                                        </div>
                                        @if ($terbaru->count() > 3)
                                            <details class="mt-2">
                                                <summary class="cursor-pointer text-sm font-bold text-slate-700 hover:underline">Lihat {{ $terbaru->count() - 3 }} tanggal lainnya</summary>
                                                @foreach ($terbaru->slice(3)->groupBy(fn ($t) => Rekap::labelBulan($t)) as $bulan => $daftar)
                                                    <p class="mt-2 text-xs font-bold uppercase tracking-wide text-slate-500">{{ $bulan }}</p>
                                                    <div class="mt-1 flex flex-wrap gap-1.5">
                                                        @foreach ($daftar as $tgl)
                                                            <a href="{{ route('guru.kegiatan', ['kelas' => $kelas->id, 'tanggal' => $tgl->toDateString()]) }}" wire:navigate
                                                                class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-md border border-garis bg-white px-2 text-sm font-bold tabular-nums text-slate-800 hover:border-slate-500"
                                                                title="{{ Rekap::tanggalPanjang($tgl) }}">{{ $tgl->day }}</a>
                                                        @endforeach
                                                    </div>
                                                @endforeach
                                            </details>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endif
                    </ul>
                @else
                    <div class="kartu p-5 text-sm text-slate-600">
                        Tidak ada yang perlu ditindaklanjuti. Semua hari kegiatan sudah dicatat dan tidak ada murid yang menunggu pemantauan.
                    </div>
                @endif
            </section>

            <section aria-labelledby="judul-bulan">
                <h2 id="judul-bulan" class="mb-3 text-lg font-extrabold">Kelas {{ $kelas->nama }}, {{ $ringkasan['bulan'] }}</h2>
                <dl class="kartu divide-y divide-garis text-sm">
                    <div class="flex items-center justify-between gap-3 px-4 py-3">
                        <dt class="text-slate-600">Hari kegiatan sejauh ini</dt>
                        <dd class="font-extrabold">{{ $ringkasan['hari'] }} hari</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-4 py-3">
                        <dt class="text-slate-600">Rata-rata hadir membaca</dt>
                        <dd><x-lencana-kategori :persen="$adaCatatan ? $ringkasan['hadir'] : null" /></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-4 py-3">
                        <dt class="text-slate-600">Rata-rata kemampuan literasi</dt>
                        <dd><x-lencana-kategori :persen="$adaCatatan ? $ringkasan['literasi'] : null" /></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-4 py-3">
                        <dt class="text-slate-600">Murid dalam pembinaan</dt>
                        <dd class="font-extrabold">{{ $ringkasan['kasus'] }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-4 py-3">
                        <dt class="text-slate-600">Karya literasi</dt>
                        <dd class="font-extrabold">{{ $ringkasan['karya'] }}</dd>
                    </div>
                </dl>
                <a href="{{ route('guru.rekap') }}" wire:navigate class="tautan mt-3 inline-block text-sm">Buka rekap lengkap</a>

                <h2 class="mb-3 mt-6 text-lg font-extrabold">Perlu didampingi membaca</h2>
                @if ($muridPerhatian->isNotEmpty())
                    <ul class="kartu divide-y divide-garis text-sm">
                        @foreach ($muridPerhatian->take(3) as $m)
                            <li class="px-4 py-2.5">
                                <p class="font-bold">{{ $m['murid']->nama }}</p>
                                <p class="text-slate-600">{{ ucfirst(implode(', ', $m['alasan'])) }}</p>
                            </li>
                        @endforeach
                    </ul>
                    @if ($muridPerhatian->count() > 3)
                        <a href="{{ route('guru.rekap', ['perhatian' => 1, 'urut' => 'terendah']) }}" wire:navigate class="tautan mt-3 inline-block text-sm">Lihat {{ $muridPerhatian->count() - 3 }} murid lainnya</a>
                    @endif
                @else
                    <p class="kartu p-4 text-sm text-slate-600">{{ ! $ringkasan['hari'] ? 'Belum ada hari kegiatan bulan ini.' : ($adaCatatan ? 'Semua murid sudah aktif membaca dengan kategori Baik atau lebih bulan ini.' : 'Belum ada catatan kegiatan bulan ini. Daftar ini terisi setelah Kegiatan Harian dicatat.') }}</p>
                @endif
            </section>
        </div>
    @endif
</div>
