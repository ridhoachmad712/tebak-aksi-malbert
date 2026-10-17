@use('App\Services\Rekap')
@use('App\Models\KasusPembinaan')

<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-sm font-bold text-brand-600">{{ Rekap::tanggalPanjang(today()) }}</p>
            <h1 class="judul-halaman">Halo, {{ str(auth()->user()->name)->before(',') }} 👋</h1>
        </div>
        <x-pilih-kelas :daftar="$this->daftarKelas" />
    </div>

    @if (! $kelas)
        <x-belum-ada-kelas />
    @else
        {{-- Pekerjaan utama hari ini --}}
        <section class="kartu overflow-hidden">
            <div class="flex flex-col gap-5 bg-gradient-to-br from-brand-500 to-orange-400 p-6 text-white sm:flex-row sm:items-center sm:justify-between sm:p-8">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wide text-orange-100">Kegiatan Harian · Kelas {{ $kelas->nama }}</p>
                    @if (! $hariKegiatan)
                        <h2 class="mt-1 text-2xl font-extrabold">Hari ini bukan hari kegiatan</h2>
                        <p class="mt-1 text-orange-50">Kegiatan Teras Baca berlangsung Senin–Jumat. Selamat beristirahat! 🌤️</p>
                    @elseif (! $kegiatanHariIni)
                        <h2 class="mt-1 text-2xl font-extrabold">Kegiatan hari ini belum dicatat</h2>
                        <p class="mt-1 text-orange-50">Centang kehadiran dan kemampuan literasi {{ $jumlahMurid }} murid.</p>
                    @else
                        <h2 class="mt-1 text-2xl font-extrabold">✓ Kegiatan hari ini sudah dicatat</h2>
                        <p class="mt-1 text-orange-50">{{ $kegiatanHariIni->hadir_count }} dari {{ $jumlahMurid }} murid hadir membaca. Anda masih bisa melengkapinya.</p>
                    @endif
                </div>
                @if (! $hariKegiatan && $hariTerakhir)
                    <a href="{{ route('guru.kegiatan', ['kelas' => $kelas->id, 'tanggal' => $hariTerakhir->toDateString()]) }}" wire:navigate
                       class="tombol shrink-0 bg-white/90 px-6 py-3.5 text-base text-brand-700 hover:bg-white">
                        Lihat catatan {{ \App\Services\Rekap::namaHari($hariTerakhir) }}
                        <x-heroicon-m-arrow-right class="h-5 w-5" />
                    </a>
                @endif
                @if ($hariKegiatan)
                    <a href="{{ route('guru.kegiatan', ['kelas' => $kelas->id]) }}" wire:navigate
                       class="tombol shrink-0 bg-white px-6 py-3.5 text-base text-brand-700 shadow-lg hover:bg-orange-50">
                        {{ $kegiatanHariIni ? 'Lanjutkan Kegiatan Hari Ini' : 'Mulai Kegiatan Hari Ini' }}
                        <x-heroicon-m-arrow-right class="h-5 w-5" />
                    </a>
                @endif
            </div>

            <div class="grid grid-cols-2 divide-x divide-orange-100 border-t border-orange-100 sm:grid-cols-4">
                <a href="{{ route('guru.pembinaan.baru') }}" wire:navigate class="flex items-center gap-3 p-4 hover:bg-orange-50">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-rose-100 text-rose-600"><x-heroicon-o-heart class="h-6 w-6" /></span>
                    <span class="text-sm font-bold leading-tight">Catat<br>Pelanggaran</span>
                </a>
                <a href="{{ route('guru.karya.baru') }}" wire:navigate class="flex items-center gap-3 p-4 hover:bg-orange-50">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-violet-100 text-violet-600"><x-heroicon-o-paint-brush class="h-6 w-6" /></span>
                    <span class="text-sm font-bold leading-tight">Tambah<br>Karya</span>
                </a>
                <a href="{{ route('guru.kegiatan', ['kelas' => $kelas->id, 'tanggal' => today()->subDay()->toDateString()]) }}" wire:navigate class="hidden items-center gap-3 p-4 hover:bg-orange-50 sm:flex">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-sky-100 text-sky-600"><x-heroicon-o-calendar-days class="h-6 w-6" /></span>
                    <span class="text-sm font-bold leading-tight">Catatan<br>Hari Lain</span>
                </a>
                <a href="{{ route('guru.rekap') }}" wire:navigate class="hidden items-center gap-3 p-4 hover:bg-orange-50 sm:flex">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-toska-100 text-toska-700"><x-heroicon-o-chart-bar class="h-6 w-6" /></span>
                    <span class="text-sm font-bold leading-tight">Rekap<br>Bulanan</span>
                </a>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Pengingat --}}
            <section class="space-y-3 lg:col-span-2">
                <h2 class="text-lg font-extrabold">Perlu perhatian</h2>

                @forelse ($perluDipantau as $k)
                    <a href="{{ route('guru.pembinaan.detail', $k) }}" wire:navigate class="kartu flex items-center gap-4 p-4 transition hover:border-brand-300">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-amber-100 text-xl">🔔</span>
                        <span class="min-w-0 flex-1">
                            <span class="block font-extrabold">{{ $k->murid->nama }} <span class="font-semibold text-slate-400">· {{ $k->kelas->nama }}</span></span>
                            <span class="block truncate text-sm text-slate-600">Perlu dipantau minggu ke-{{ implode(', ', $k->mingguBelumDipantau()) }} · {{ $k->namaPelanggaran() }}</span>
                        </span>
                        <span class="tombol-kedua hidden shrink-0 sm:inline-flex">Pantau</span>
                    </a>
                @empty
                @endforelse

                @foreach ($siapDitutup as $k)
                    <a href="{{ route('guru.pembinaan.detail', $k) }}" wire:navigate class="kartu flex items-center gap-4 p-4 transition hover:border-brand-300">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-toska-100 text-xl">🏁</span>
                        <span class="min-w-0 flex-1">
                            <span class="block font-extrabold">{{ $k->murid->nama }}</span>
                            <span class="block text-sm text-slate-600">Pemantauan 4 minggu selesai. Tetapkan status akhir.</span>
                        </span>
                    </a>
                @endforeach

                @foreach ($belumDicatat as $tgl)
                    <a href="{{ route('guru.kegiatan', ['kelas' => $kelas->id, 'tanggal' => $tgl->toDateString()]) }}" wire:navigate class="kartu flex items-center gap-4 p-4 transition hover:border-brand-300">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-rose-100 text-xl">📅</span>
                        <span class="min-w-0 flex-1">
                            <span class="block font-extrabold">{{ Rekap::tanggalPanjang($tgl) }}</span>
                            <span class="block text-sm text-slate-600">Kegiatan harian Kelas {{ $kelas->nama }} belum dicatat</span>
                        </span>
                        <span class="tombol-kedua hidden shrink-0 sm:inline-flex">Catat</span>
                    </a>
                @endforeach

                @if ($perluDipantau->isEmpty() && $siapDitutup->isEmpty() && $belumDicatat->isEmpty())
                    <div class="kartu p-6 text-center">
                        <div class="text-4xl">🌟</div>
                        <p class="mt-2 font-bold">Semua beres! Tidak ada yang perlu ditindaklanjuti.</p>
                    </div>
                @endif
            </section>

            {{-- Ringkasan bulan ini --}}
            <section>
                <h2 class="mb-3 text-lg font-extrabold">{{ $ringkasan['bulan'] }}</h2>
                <div class="kartu divide-y divide-orange-50">
                    @php
                        $baris = [
                            ['📖', 'Hari kegiatan', $ringkasan['hari'].' hari', null],
                            ['🙋', 'Rata-rata hadir membaca', $ringkasan['hadir'] !== null ? $ringkasan['hadir'].'%' : '-', $ringkasan['hadir']],
                            ['💡', 'Rata-rata kemampuan literasi', $ringkasan['literasi'] !== null ? $ringkasan['literasi'].'%' : '-', $ringkasan['literasi']],
                            ['🤝', 'Murid dalam pembinaan', $ringkasan['kasus'].' murid', null],
                            ['🎨', 'Karya literasi', $ringkasan['karya'].' karya', null],
                        ];
                    @endphp
                    @foreach ($baris as [$ikon, $label, $nilai, $persen])
                        <div class="flex items-center gap-3 px-4 py-3">
                            <span class="text-xl">{{ $ikon }}</span>
                            <span class="flex-1 text-sm font-semibold text-slate-600">{{ $label }}</span>
                            @if ($persen !== null)
                                <x-lencana-kategori :persen="$persen" :teks="$nilai" />
                            @else
                                <span class="font-extrabold">{{ $nilai }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    @endif
</div>
