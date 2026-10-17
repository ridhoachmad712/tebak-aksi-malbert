<div class="space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="judul-halaman">Rekap</h1>
            <p class="teks-bantu">Disusun dari catatan harian. Angka 18/20 berarti 18 centang dari 20 hari kegiatan.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-pilih-kelas :daftar="$this->daftarKelas" />
            <select wire:model.live="periode" aria-label="Jenis periode" class="isian w-auto pr-9 font-bold">
                <option value="bulan">Per bulan</option>
                <option value="semester">Per semester</option>
            </select>
            @if ($periode === 'semester')
                <select wire:model.live="semester" aria-label="Semester" class="isian w-auto pr-9 font-bold">
                    <option value="1">Semester Ganjil</option>
                    <option value="2">Semester Genap</option>
                </select>
            @else
                <input type="month" wire:model.live="bulan" max="{{ today()->format('Y-m') }}" aria-label="Bulan" class="isian w-auto font-bold">
            @endif
        </div>
    </div>

    @if (! $this->kelas)
        <x-belum-ada-kelas />
    @else
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap gap-2" role="tablist">
                @foreach ($daftarLaporan as $nilai => $label)
                    <button wire:click="$set('laporan', '{{ $nilai }}')" role="tab" aria-selected="{{ $laporan === $nilai ? 'true' : 'false' }}"
                        @class(['tab', 'tab-aktif' => $laporan === $nilai, 'tab-pasif' => $laporan !== $nilai])>{{ $label }}</button>
                @endforeach
            </div>
            <div class="flex gap-2">
                <a href="{{ route('rekap.unduh', ['kelas' => $kelasId, 'laporan' => $laporan, 'format' => 'pdf'] + $this->parameterPeriode()) }}" target="_blank" class="tombol-kedua"><x-heroicon-o-printer class="h-4 w-4" /> Cetak PDF</a>
                <a href="{{ route('rekap.unduh', ['kelas' => $kelasId, 'laporan' => $laporan, 'format' => 'xlsx'] + $this->parameterPeriode()) }}" class="tombol-kedua"><x-heroicon-o-arrow-down-tray class="h-4 w-4" /> Excel</a>
            </div>
        </div>

        <section class="kartu overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-garis px-4 py-3">
                <h2 class="font-extrabold">{{ $daftarLaporan[$laporan] }}, Kelas {{ $this->kelas->nama }}, {{ $labelPeriode }}</h2>
                <span class="text-sm font-semibold text-slate-600">{{ $rekap['n'] }} hari kegiatan</span>
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
