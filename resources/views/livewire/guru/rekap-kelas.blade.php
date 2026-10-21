@use('App\Services\Rekap')

<div class="space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="judul-halaman">Rekap</h1>
            <p class="teks-bantu">Disusun dari catatan harian. Angka 18/20 berarti 18 centang dari 20 hari kegiatan.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-pilih-kelas :daftar="$this->daftarKelas" />
            <x-pilih-periode :periode="$periode" />
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
                <a href="{{ route('rekap.unduh', ['kelas' => $kelasId, 'laporan' => $laporan, 'format' => 'pdf'] + $unduh) }}" target="_blank" class="tombol-kedua"><x-heroicon-o-printer class="h-4 w-4" /> Cetak PDF</a>
                <a href="{{ route('rekap.unduh', ['kelas' => $kelasId, 'laporan' => $laporan, 'format' => 'xlsx'] + $unduh) }}" class="tombol-kedua"><x-heroicon-o-arrow-down-tray class="h-4 w-4" /> Excel</a>
            </div>
        </div>

        <section class="kartu overflow-hidden" wire:loading.class="opacity-60">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-garis px-4 py-3">
                <div>
                    <h2 class="font-extrabold">{{ $daftarLaporan[$laporan] }}, Kelas {{ $this->kelas->nama }}, {{ $labelPeriode }}</h2>
                    <p class="text-sm text-slate-700">
                        {{ $rekap['n'] }} hari kegiatan
                        @isset($ringkasan)
                            · {{ $laporan === 'ekosistem' ? 'Rata-rata kehadiran murid' : 'Rata-rata kelas' }} <b class="tabular-nums">{{ Rekap::persenTeks($ringkasan['rerata']) }}</b>
                            · <b class="tabular-nums">{{ $ringkasan['perhatian'] }}</b> dari {{ $ringkasan['jumlah'] }} murid perlu perhatian
                        @else
                            · <b class="tabular-nums">{{ $kasus->count() }}</b> kasus, {{ $kasus->whereNull('status_akhir')->count() }} masih dibina
                        @endisset
                    </p>
                </div>
                @isset($ringkasan)
                    <div class="flex flex-wrap items-center gap-3">
                        <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-bold">
                            <input type="checkbox" wire:model.live="hanyaPerhatian" class="h-5 w-5 rounded border-slate-400">
                            Hanya yang perlu perhatian
                        </label>
                        <select wire:model.live="urut" aria-label="Urutan" class="isian w-auto pr-9 text-sm font-bold">
                            @foreach (\App\Livewire\Guru\RekapKelas::URUTAN as $nilai => $teks)
                                <option value="{{ $nilai }}">{{ $teks }}</option>
                            @endforeach
                        </select>
                    </div>
                @endisset
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
