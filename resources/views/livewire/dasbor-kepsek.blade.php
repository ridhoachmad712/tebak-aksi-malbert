@use('App\Services\Rekap')
@use('App\Services\TugasAdmin')
@php
    $parameter = ['bulan' => $bulan];
    $jumlahTindakan = $belumBerubah->count() + $pelanggaranBerat->count();
@endphp

<div class="space-y-6">
    <div>
        <h1 class="judul-halaman">{{ \App\Services\DasborKepsek::sapaan() }}, {{ auth()->user()->name }}</h1>
        <p class="teks-bantu">{{ Rekap::tanggalPanjang(today()) }}{{ $ta ? ' · Tahun ajaran '.$ta->nama : '' }}</p>
    </div>

    @unless ($ta)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 font-semibold text-amber-900">Tahun ajaran aktif belum diatur, sehingga capaian belum bisa dihitung. Admin dapat mengaturnya di Halaman Admin, menu Tahun Ajaran.</div>
    @endunless

    {{-- Capaian lima aspek inovasi, berfokus pada jumlah murid seperti laporan hasil inovasi sekolah --}}
    @php
        $statusAspek = collect($aspekInovasi['aspek'])->countBy('status');
        $bagianRingkas = array_filter([
            ($statusAspek['tercapai'] ?? 0) ? ($statusAspek['tercapai']).' aspek tercapai' : null,
            ($statusAspek['kurang'] ?? 0) ? ($statusAspek['kurang']).' aspek belum tercapai' : null,
            (($statusAspek['tanpa_data'] ?? 0) + ($statusAspek['belum_cukup'] ?? 0)) ? (($statusAspek['tanpa_data'] ?? 0) + ($statusAspek['belum_cukup'] ?? 0)).' aspek belum dapat dinilai' : null,
        ]);
    @endphp
    <section class="kartu" aria-labelledby="judul-status" wire:loading.class="opacity-60">
        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-garis px-5 py-4">
            <div class="min-w-0">
                <h2 id="judul-status" class="text-xs font-extrabold uppercase tracking-wide text-slate-600">Capaian 5 aspek inovasi</h2>
                <p class="mt-1 text-base font-bold text-slate-900">{{ $labelBulanAspek }}: {{ $bagianRingkas ? implode(', ', $bagianRingkas) : 'belum ada catatan yang cukup untuk dinilai' }}.</p>
                @if (isset($aspekInovasi['sumber_historis']))
                    {{-- Sumber rekap historis tetap disebut; keterangannya bisa dibuka --}}
                    <details class="mt-0.5 text-xs text-slate-600">
                        <summary class="cursor-pointer">Sumber: rekap historis sekolah</summary>
                        <p class="mt-1">{{ $aspekInovasi['sumber_historis'] }}, {{ $aspekInovasi['jumlah_murid'] }} murid.</p>
                        @if ($aspekInovasi['catatan_historis'])
                            <p class="mt-1">{{ $aspekInovasi['catatan_historis'] }}</p>
                        @endif
                    </details>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="bulan" aria-label="Bulan" class="isian w-auto pr-9 font-bold">
                    @foreach (Rekap::daftarBulan() as $nilai => $teks)
                        <option value="{{ $nilai }}">{{ $teks }}</option>
                    @endforeach
                </select>
                <a href="{{ route('monev', $parameter) }}" wire:navigate class="tombol-kedua">Monev lengkap</a>
                <a href="{{ route('monev.cetak', $parameter) }}" target="_blank" class="tombol-kedua"><x-heroicon-o-printer class="h-4 w-4" /> Cetak ringkasan Monev</a>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 p-4 lg:grid-cols-5">
            @foreach ($aspekInovasi['aspek'] as $a)
                @include('dasbor.kartu-aspek', ['a' => $a, 'akhir' => $loop->last])
            @endforeach
        </div>
    </section>

    {{-- Inti dashboard: hal yang butuh keputusan kepala sekolah, satu baris per murid --}}
    @php
        $daftarTindakan = $pelanggaranBerat->map(fn ($k) => ['kasus' => $k, 'berat' => true])
            ->concat($belumBerubah->map(fn ($k) => ['kasus' => $k, 'berat' => false]));
    @endphp
    <section class="kartu" aria-labelledby="judul-tindakan">
        <h2 id="judul-tindakan" class="border-b border-garis px-4 py-3 font-extrabold">Perlu tindakan Bapak/Ibu{{ $jumlahTindakan ? ' ('.$jumlahTindakan.')' : '' }}</h2>
        @if ($jumlahTindakan)
            <ul class="divide-y divide-garis text-sm">
                @foreach ($daftarTindakan->take(5) as $t)
                    @php $k = $t['kasus']; @endphp
                    <li>
                        <details class="group px-4 py-2.5">
                            <summary class="flex cursor-pointer list-none flex-wrap items-center justify-between gap-2">
                                <span><span class="font-bold">{{ $k->murid->nama }}</span> <span class="text-slate-600">· Kelas {{ $k->kelas->nama }}</span></span>
                                <span class="flex shrink-0 items-center gap-1.5">
                                    @if ($t['berat'])
                                        <span class="lencana bg-rose-50 text-rose-800 ring-1 ring-inset ring-rose-200">Pelanggaran berat</span>
                                    @else
                                        <span class="lencana bg-amber-50 text-amber-900 ring-1 ring-inset ring-amber-200">Belum berubah 4 minggu</span>
                                    @endif
                                    <x-heroicon-o-chevron-down class="h-4 w-4 text-slate-500 transition group-open:rotate-180" aria-hidden="true" />
                                </span>
                            </summary>
                            <p class="mt-1 text-slate-700">{{ $k->namaPelanggaran() }} · pembinaan: {{ $k->namaTindakan() }} · {{ $t['berat'] ? 'dicatat '.TugasAdmin::teksTerakhir($k->tanggal) : 'sejak '.$k->tanggal->format('d/m/Y') }}</p>
                        </details>
                    </li>
                @endforeach
            </ul>
            @if ($jumlahTindakan > 5)
                <a href="{{ route('monev', $parameter) }}" wire:navigate class="block border-t border-garis px-4 py-2.5 text-sm font-bold underline decoration-slate-300 underline-offset-4 hover:decoration-slate-700">Lihat {{ $jumlahTindakan - 5 }} lainnya di Monev</a>
            @endif
        @else
            <p class="px-4 py-3 text-sm text-slate-700">Tidak ada yang perlu ditindaklanjuti saat ini.</p>
        @endif
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Hanya kelas yang tertinggal yang ditampilkan; daftar lengkap bisa dibuka --}}
        @php $kelasTertinggal = $pencatatan->filter(fn ($k) => $k['jumlah_murid'] > 0 && $k['hari_terlewat'] >= 2); @endphp
        <section class="kartu min-w-0 self-start overflow-hidden" aria-labelledby="judul-pencatatan">
            <h2 id="judul-pencatatan" class="border-b border-garis px-4 py-3 font-extrabold">Pencatatan kelas minggu ini</h2>
            @if ($pencatatan->isEmpty())
                <p class="px-4 py-3 text-sm text-slate-700">Belum ada kelas pada tahun ajaran aktif. Admin dapat menambahkannya di Halaman Admin, menu Kelas.</p>
            @else
                @if ($kelasTertinggal->isNotEmpty())
                    <ul class="divide-y divide-garis text-sm">
                        @foreach ($kelasTertinggal as $k)
                            <li class="flex items-center justify-between gap-3 px-4 py-2.5">
                                <span><span class="font-bold">{{ $k['kelas'] }}</span> <span class="text-slate-600">· {{ $k['wali'] ?? 'Belum ada wali kelas' }}</span></span>
                                <span class="shrink-0 rounded-md bg-amber-50 px-1.5 py-0.5 text-xs font-bold text-amber-900 ring-1 ring-inset ring-amber-200">Terakhir {{ TugasAdmin::teksTerakhir($k['terakhir']) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="px-4 py-3 text-sm text-slate-700">Semua {{ $pencatatan->count() }} kelas sudah mencatat kegiatan minggu ini.</p>
                @endif
                <details class="border-t border-garis text-sm">
                    <summary class="cursor-pointer px-4 py-2.5 font-bold text-slate-700">Lihat semua kelas</summary>
                    <table class="w-full">
                        <thead class="tabel-kepala">
                            <tr>
                                <th class="px-4 py-2">Kelas</th>
                                <th class="px-3 py-2">Terakhir dicatat</th>
                                <th class="px-3 py-2 text-right">Minggu ini</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-garis">
                            @foreach ($pencatatan as $k)
                                <tr>
                                    <td class="px-4 py-2 font-bold">{{ $k['kelas'] }}</td>
                                    <td class="px-3 py-2 text-slate-700">{{ $k['jumlah_murid'] ? TugasAdmin::teksTerakhir($k['terakhir']) : 'Belum ada murid' }}</td>
                                    <td class="px-3 py-2 text-right font-bold tabular-nums">{{ $k['hari_minggu_ini'] ? $k['minggu_ini'].'/'.$k['hari_minggu_ini'].' hari' : '–' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </details>
            @endif
        </section>

        <section class="kartu min-w-0 self-start" aria-labelledby="judul-kabar">
            <h2 id="judul-kabar" class="border-b border-garis px-4 py-3 font-extrabold">Kabar baik</h2>
            <ul class="divide-y divide-garis text-sm">
                @if ($kelasNaik)
                    <li class="px-4 py-2.5"><span class="font-bold">Kelas {{ $kelasNaik['kelas']->nama }}</span> paling meningkat: murid aktif naik {{ Rekap::persenTeks($kelasNaik['naik'], false) }} poin dibanding {{ $labelLalu }}.</li>
                @endif
                @foreach ($karya as $k)
                    <li class="flex items-center justify-between gap-3 px-4 py-2.5">
                        <span class="min-w-0 truncate"><span class="font-bold">{{ $k->judul }}</span> <span class="text-slate-600">· {{ $k->namaJenis() }}</span></span>
                        <span class="shrink-0 text-xs text-slate-600">Kelas {{ $k->kelas->nama }}</span>
                    </li>
                @endforeach
                @if (! $kelasNaik && $karya->isEmpty())
                    <li class="px-4 py-3 text-slate-700">Belum ada karya literasi yang dicatat. Karya akan muncul di sini setelah guru mencatatnya.</li>
                @endif
            </ul>
        </section>
    </div>
</div>
