@use('App\Services\Rekap')
@php $parameter = $this->parameterPeriode(); @endphp

<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="judul-halaman">Monev TEBAK AKSI</h1>
            <p class="teks-bantu">Seluruh sekolah · {{ $label }} · {{ $hari }} hari kegiatan</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <x-pilih-periode :periode="$periode" />
            <a href="{{ route('monev.cetak', $parameter) }}" target="_blank" class="tombol-utama"><x-heroicon-o-printer class="h-4 w-4" /> Cetak ringkasan</a>
        </div>
    </div>

    @unless ($ta)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 font-semibold text-amber-900">Tahun ajaran aktif belum diatur. Admin dapat mengaturnya di Halaman Admin, menu Tahun Ajaran.</div>
    @endunless

    <x-pita-rekonstruksi :dari="$this->dataPeriode()['dari']" :sampai="$this->dataPeriode()['sampai']" />

    @include('monev.ringkasan')

    {{-- Titik fokus: yang perlu ditindaklanjuti kepala sekolah --}}
    <section class="kartu" aria-labelledby="judul-perhatian" wire:loading.class="opacity-60">
        <h2 id="judul-perhatian" class="border-b border-garis px-4 py-3 font-extrabold">Perlu perhatian</h2>
        <div class="grid divide-y divide-garis lg:grid-cols-2 lg:divide-x lg:divide-y-0">
            <div>
                <h3 class="px-4 pt-3 text-sm font-bold text-slate-700">Kelas dengan murid aktif di bawah target {{ Rekap::persenTeks((float) $indikator['murid_aktif']['target']) }}</h3>
                @if ($indikator['kelas_di_bawah_target']->isNotEmpty())
                    <ul class="divide-y divide-garis text-sm">
                        @foreach ($indikator['kelas_di_bawah_target']->sortBy('persen') as $r)
                            <li class="flex items-center justify-between gap-3 px-4 py-2.5">
                                <a href="{{ route('monev.kelas', ['kelas' => $r['kelas']->id] + $parameter) }}" wire:navigate class="font-bold underline decoration-slate-300 underline-offset-4 hover:decoration-slate-700">Kelas {{ $r['kelas']->nama }}</a>
                                <span class="text-slate-700">{{ $r['aktif'] }} dari {{ $r['jumlah'] }} murid aktif <x-nilai-target :nilai="$r['persen']" :target="$indikator['murid_aktif']['target']" class="ml-1" /></span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="px-4 py-3 text-sm text-slate-700">Semua kelas sudah mencapai target murid aktif pada periode ini.</p>
                @endif
            </div>
            <div>
                <h3 class="px-4 pt-3 text-sm font-bold text-slate-700">Murid belum berubah setelah 4 minggu pembinaan</h3>
                @include('monev.daftar-tindak-lanjut', ['kasus' => $indikator['tindak_lanjut'], 'tampilKelas' => true])
            </div>
        </div>
    </section>

    <section class="kartu overflow-hidden" aria-labelledby="judul-indikator" wire:loading.class="opacity-60">
        <h2 id="judul-indikator" class="border-b border-garis px-4 py-3 font-extrabold">Capaian per pilar program</h2>
        @include('monev.tabel-pilar')
    </section>

    <section class="kartu overflow-hidden" aria-labelledby="judul-kelas" wire:loading.class="opacity-60">
        <h2 id="judul-kelas" class="border-b border-garis px-4 py-3 font-extrabold">Peta kelas</h2>
        @include('monev.peta-kelas')
    </section>

    {{-- Rincian pendukung --}}
    <div class="grid gap-6 lg:grid-cols-2">
        <section class="kartu p-5" aria-labelledby="judul-tren">
            <h2 id="judul-tren" class="font-extrabold">Rata-rata kehadiran membaca per bulan</h2>
            <p class="text-xs text-slate-600">Tahun ajaran {{ $ta?->nama }}, seluruh kelas. Garis putus-putus = target murid aktif.</p>
            @if ($tren)
                <div class="relative mt-4 flex h-44 items-end gap-3 border-b border-slate-300">
                    <div class="pointer-events-none absolute inset-x-0 border-t-2 border-dashed border-slate-400" style="bottom: {{ min(100, $indikator['murid_aktif']['target']) }}%"></div>
                    @foreach ($tren as $t)
                        <div class="flex h-full flex-1 flex-col items-center justify-end gap-1">
                            <span class="text-xs font-bold tabular-nums text-slate-700">{{ $t['hadir'] !== null ? round($t['hadir']).'%' : '–' }}</span>
                            <div class="w-full max-w-12 rounded-t-md bg-slate-600" style="height: {{ max(0, min(100, $t['hadir'] ?? 0)) }}%"></div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-1 flex gap-3">
                    @foreach ($tren as $t)
                        <span class="flex-1 text-center text-xs font-semibold text-slate-600">{{ $t['label'] }}</span>
                    @endforeach
                </div>
                <table class="mt-4 w-full text-sm">
                    <caption class="mb-1 text-left text-xs font-bold text-slate-600">Kasus pelanggaran baru per bulan</caption>
                    <tr class="divide-x divide-garis border border-garis">
                        @foreach ($tren as $t)
                            <td class="px-2 py-1.5 text-center"><span class="block text-xs text-slate-600">{{ $t['label'] }}</span><span class="font-extrabold tabular-nums">{{ $t['kasus'] }}</span></td>
                        @endforeach
                    </tr>
                </table>
            @else
                <p class="mt-3 text-sm text-slate-600">Belum ada data. Atur tahun ajaran aktif terlebih dahulu.</p>
            @endif
        </section>

        <section class="kartu overflow-hidden" aria-labelledby="judul-aspek">
            <div class="border-b border-garis px-4 py-3">
                <h2 id="judul-aspek" class="font-extrabold">Jumlah kasus per aspek karakter</h2>
                <p class="text-xs text-slate-600">{{ $label }}. Aspek diambil dari jenis pelanggaran yang dicatat guru.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="tabel-kepala">
                        <tr>
                            <th class="px-4 py-2.5">Kelas</th>
                            @foreach ($indikator['aspek']['aspek'] as $a)
                                <th class="px-2 py-2.5 text-center">{{ $a }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-garis">
                        @foreach ($indikator['aspek']['kelas'] as $r)
                            <tr>
                                <td class="px-4 py-2 font-bold">{{ $r['kelas']->nama }}</td>
                                @foreach ($indikator['aspek']['aspek'] as $a)
                                    @php $j = $r['hitung'][$a] ?? 0; @endphp
                                    <td @class(['px-2 py-2 text-center tabular-nums', 'font-extrabold text-rose-800' => $j > 0 && $a === $r['tertinggi'], 'text-slate-500' => $j === 0])>{{ $j }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="border-t border-garis px-4 py-2 text-xs text-slate-600">Angka merah tebal menandai aspek dengan kasus terbanyak di kelas tersebut.</p>
        </section>
    </div>

    @include('monev.unduh', ['keterangan' => 'Seluruh kelas'])
</div>
