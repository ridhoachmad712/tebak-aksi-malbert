@use('App\Services\Rekap')

<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-sm font-semibold text-slate-600">{{ Rekap::tanggalPanjang(today()) }}</p>
            <h1 class="judul-halaman">Halo, {{ str(auth()->user()->name)->before(',') }}</h1>
        </div>
        <x-pilih-kelas :daftar="$this->daftarKelas" />
    </div>

    {{-- Panduan singkat untuk pengguna baru; disembunyikan setelah ditutup --}}
    <div x-data="{ tampil: (() => { try { return localStorage.getItem('panduan-tutup') !== '1' } catch (e) { return true } })() }"
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
                            <li>
                                <a href="{{ route('guru.pembinaan.detail', $k) }}" wire:navigate class="flex items-center gap-3 px-4 py-3 hover:bg-krem">
                                    <x-heroicon-o-clipboard-document-check class="h-6 w-6 shrink-0 text-amber-700" />
                                    <span class="min-w-0 flex-1">
                                        <span class="block font-bold">Pantau {{ $k->murid->nama }} <span class="font-semibold text-slate-600">· Kelas {{ $k->kelas->nama }}</span></span>
                                        <span class="block truncate text-sm text-slate-600">Minggu ke-{{ implode(', ', $k->mingguBelumDipantau()) }} belum diisi · {{ $k->namaPelanggaran() }}</span>
                                    </span>
                                    <x-heroicon-m-chevron-right class="h-5 w-5 text-slate-400" />
                                </a>
                            </li>
                        @endforeach
                        @foreach ($siapDitutup as $k)
                            <li>
                                <a href="{{ route('guru.pembinaan.detail', $k) }}" wire:navigate class="flex items-center gap-3 px-4 py-3 hover:bg-krem">
                                    <x-heroicon-o-flag class="h-6 w-6 shrink-0 text-toska-700" />
                                    <span class="min-w-0 flex-1">
                                        <span class="block font-bold">Tetapkan status akhir {{ $k->murid->nama }}</span>
                                        <span class="block text-sm text-slate-600">Pemantauan 4 minggu sudah lengkap.</span>
                                    </span>
                                    <x-heroicon-m-chevron-right class="h-5 w-5 text-slate-400" />
                                </a>
                            </li>
                        @endforeach
                        @foreach ($belumDicatat as $tgl)
                            <li>
                                <a href="{{ route('guru.kegiatan', ['kelas' => $kelas->id, 'tanggal' => $tgl->toDateString()]) }}" wire:navigate class="flex items-center gap-3 px-4 py-3 hover:bg-krem">
                                    <x-heroicon-o-calendar class="h-6 w-6 shrink-0 text-rose-700" />
                                    <span class="min-w-0 flex-1">
                                        <span class="block font-bold">Catat {{ Rekap::tanggalPanjang($tgl) }}</span>
                                        <span class="block text-sm text-slate-600">Kegiatan harian Kelas {{ $kelas->nama }} belum dicatat.</span>
                                    </span>
                                    <x-heroicon-m-chevron-right class="h-5 w-5 text-slate-400" />
                                </a>
                            </li>
                        @endforeach
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
                        <dd><x-lencana-kategori :persen="$ringkasan['hadir']" /></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-4 py-3">
                        <dt class="text-slate-600">Rata-rata kemampuan literasi</dt>
                        <dd><x-lencana-kategori :persen="$ringkasan['literasi']" /></dd>
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
            </section>
        </div>
    @endif
</div>
