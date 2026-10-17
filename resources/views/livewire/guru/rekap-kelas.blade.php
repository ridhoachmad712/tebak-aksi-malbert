@use('App\Models\KasusPembinaan')

<div class="space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="judul-halaman">Rekap Bulanan</h1>
            <p class="text-sm text-slate-500">Dihitung otomatis dari catatan harian. Angka = jumlah centang dari jumlah hari kegiatan.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-pilih-kelas :daftar="$this->daftarKelas" />
            <input type="month" wire:model.live="bulan" max="{{ today()->format('Y-m') }}" class="isian w-auto py-2 font-bold">
        </div>
    </div>

    @if (! $this->kelas)
        <x-belum-ada-kelas />
    @else
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap gap-2">
                @foreach ($daftarLaporan as $nilai => $label)
                    <button wire:click="$set('laporan', '{{ $nilai }}')"
                        @class(['rounded-full px-4 py-2 text-sm font-bold transition',
                            'bg-slate-800 text-white' => $laporan === $nilai,
                            'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50' => $laporan !== $nilai])>{{ $label }}</button>
                @endforeach
            </div>
            <div class="flex gap-2">
                <a href="{{ route('rekap.unduh', ['kelas' => $kelasId, 'bulan' => $bulan, 'laporan' => $laporan, 'format' => 'pdf']) }}" target="_blank" class="tombol-kedua"><x-heroicon-o-printer class="h-4 w-4" /> Cetak PDF</a>
                <a href="{{ route('rekap.unduh', ['kelas' => $kelasId, 'bulan' => $bulan, 'laporan' => $laporan, 'format' => 'xlsx']) }}" class="tombol-kedua"><x-heroicon-o-table-cells class="h-4 w-4" /> Excel</a>
            </div>
        </div>

        <section class="kartu overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-orange-100 px-4 py-3">
                <h2 class="font-extrabold">{{ $daftarLaporan[$laporan] }} · Kelas {{ $this->kelas->nama }} · {{ $labelBulan }}</h2>
                <span class="text-sm font-bold text-slate-500">{{ $rekap['n'] }} hari kegiatan</span>
            </div>

            <div class="overflow-x-auto">
                @include('rekap.tabel-'.$laporan)
            </div>
        </section>

        @if (in_array($laporan, ['budaya', 'literasi', 'ekosistem']))
            <x-keterangan-kategori />
        @endif
    @endif
</div>
