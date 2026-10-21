@use('App\Services\Rekap')
@php $parameter = $this->parameterPeriode(); @endphp

<div class="space-y-6">
    <div>
        <a href="{{ route('monev', $parameter) }}" wire:navigate class="inline-flex items-center gap-1 text-sm font-bold text-slate-700 hover:underline"><x-heroicon-o-arrow-left class="h-4 w-4" /> Monev seluruh sekolah</a>
        <div class="mt-2 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="judul-halaman">Kelas {{ $kelas->nama }}</h1>
                <p class="teks-bantu">Wali kelas: {{ $kelas->waliKelas?->name ?? 'belum ada' }} · {{ $ringkasKelas['jumlah'] }} murid · {{ $label }} · {{ $hari }} hari kegiatan</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-pilih-periode :periode="$periode" />
                <a href="{{ route('guru.rekap', ['kelas' => $kelas->id] + $parameter) }}" wire:navigate class="tombol-kedua"><x-heroicon-o-table-cells class="h-4 w-4" /> Rekap per murid</a>
            </div>
        </div>
    </div>

    <x-pita-rekonstruksi :dari="$this->dataPeriode()['dari']" :sampai="$this->dataPeriode()['sampai']" />

    @include('monev.ringkasan')

    <section class="kartu" aria-labelledby="judul-perhatian" wire:loading.class="opacity-60">
        <h2 id="judul-perhatian" class="border-b border-garis px-4 py-3 font-extrabold">Perlu perhatian</h2>
        <div class="grid divide-y divide-garis lg:grid-cols-2 lg:divide-x lg:divide-y-0">
            <div>
                <h3 class="px-4 pt-3 text-sm font-bold text-slate-700">Murid perlu perhatian ({{ $muridPerhatian->count() }} dari {{ $ringkasKelas['jumlah'] }})</h3>
                @if ($muridPerhatian->isNotEmpty())
                    <ul class="divide-y divide-garis text-sm">
                        @foreach ($muridPerhatian->take(10) as $m)
                            <li class="px-4 py-2.5">
                                <p class="font-bold">{{ $m['murid']->nama }}</p>
                                <p class="text-slate-700">{{ ucfirst(implode(', ', $m['alasan'])) }}</p>
                            </li>
                        @endforeach
                    </ul>
                    @if ($muridPerhatian->count() > 10)
                        <a href="{{ route('guru.rekap', ['kelas' => $kelas->id, 'perhatian' => 1, 'urut' => 'terendah'] + $parameter) }}" wire:navigate class="block border-t border-garis px-4 py-2.5 text-sm font-bold underline decoration-slate-300 underline-offset-4 hover:decoration-slate-700">Lihat {{ $muridPerhatian->count() - 10 }} murid lainnya di Rekap</a>
                    @endif
                @else
                    <p class="px-4 py-3 text-sm text-slate-700">{{ $hari ? 'Semua murid aktif membaca dan semuanya sudah kategori Baik atau Sangat Baik.' : 'Belum ada hari kegiatan pada periode ini.' }}</p>
                @endif
                <p class="px-4 pb-3 text-xs text-slate-600">Masuk daftar bila belum aktif membaca, atau Budaya Membaca / Kemampuan Literasi di bawah {{ Rekap::persenTeks(\App\Models\Pengaturan::angka('batas_baik')) }} (kategori Cukup atau Perlu Bimbingan).</p>
            </div>
            <div>
                <h3 class="px-4 pt-3 text-sm font-bold text-slate-700">Kasus dalam pembinaan ({{ $kasusAktif->count() }})</h3>
                <p class="px-4 py-2 text-xs text-slate-600">Kasus terjadi dalam periode terpilih dan masih terbuka saat ini.</p>
                @include('monev.daftar-tindak-lanjut', ['kasus' => $kasusAktif, 'tampilKelas' => false, 'kosong' => 'Tidak ada kasus dalam periode terpilih yang masih dibina. Pilih periode lain untuk melihat kasus lainnya.'])
            </div>
        </div>
    </section>

    <section class="kartu overflow-hidden" aria-labelledby="judul-indikator" wire:loading.class="opacity-60">
        <h2 id="judul-indikator" class="border-b border-garis px-4 py-3 font-extrabold">Capaian kelas per pilar program</h2>
        @include('monev.tabel-pilar')
    </section>

    @include('monev.unduh', ['keterangan' => 'Kelas '.$kelas->nama])
</div>
