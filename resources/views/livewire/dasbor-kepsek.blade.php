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

    {{-- Capaian lima aspek inovasi, dihitung per jumlah murid seperti laporan hasil inovasi sekolah --}}
    <section class="kartu" aria-labelledby="judul-status" wire:loading.class="opacity-60">
        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-garis px-5 py-4">
            <div>
                <h2 id="judul-status" class="text-xs font-extrabold uppercase tracking-wide text-slate-600">Capaian 5 aspek inovasi</h2>
                <p class="mt-1 text-base font-bold text-slate-900">
                    @if ($aspekInovasi['dinilai'])
                        {{ $labelBulanAspek }}: {{ $aspekInovasi['tercapai'] }} dari {{ $aspekInovasi['dinilai'] }} aspek mencapai target.
                    @else
                        {{ $labelBulanAspek }}: belum ada catatan yang cukup untuk dinilai.
                    @endif
                </p>
                <p class="text-xs text-slate-600">Dihitung dari {{ $aspekInovasi['jumlah_murid'] }} murid aktif berdasarkan catatan guru.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="bulan" aria-label="Bulan" class="isian w-auto pr-9 font-bold">
                    @foreach (Rekap::daftarBulan() as $nilai => $teks)
                        <option value="{{ $nilai }}">{{ $teks }}</option>
                    @endforeach
                </select>
                <a href="{{ route('monev', ['bulan' => $bulan]) }}" wire:navigate class="tombol-kedua">Lihat Monev lengkap</a>
            </div>
        </div>
        {{-- Lima kartu singkat: angka besar lebih dulu, rincian di bawahnya --}}
        <div class="grid grid-cols-2 gap-3 p-4 lg:grid-cols-5">
            @foreach ($aspekInovasi['aspek'] as $a)
                <div @class(["flex flex-col rounded-lg border border-garis px-4 py-3", "col-span-2 lg:col-span-1" => $loop->last]) title="{{ ucfirst($a['uraian']) }}">
                    <p class="text-sm font-bold text-slate-800">{{ $a['nama'] }}</p>
                    <p class="mt-1 text-3xl leading-none">
                        @if ($a['status'] === 'tanpa_data')
                            <span class="font-bold text-slate-400">–</span>
                        @else
                            <x-nilai-target :nilai="$a['nilai']" :target="$a['target']" class="px-1" />
                        @endif
                    </p>
                    <p class="mt-1 text-xs text-slate-600">target ≥ {{ Rekap::persenTeks((float) $a['target']) }}</p>
                    @isset($a['nilai2'])
                        <p class="mt-1 text-xs text-slate-600">berkarya <x-nilai-target :nilai="$a['nilai2']" :target="$a['target2']" class="text-sm" /> · target ≥ {{ Rekap::persenTeks((float) $a['target2']) }}</p>
                    @endisset
                    <p class="mt-2 text-sm text-slate-800">{{ $a['capaian'] }}</p>
                    @isset($a['rincian'])
                        <p class="mt-0.5 text-xs text-slate-600">{{ $a['rincian'] }}</p>
                    @endisset
                    <p class="mt-auto pt-2 text-xs font-bold {{ $a['status'] === 'kurang' ? 'text-slate-900' : 'text-slate-600' }}">
                        @if ($a['status'] === 'tanpa_data')
                            <span class="font-normal">{{ $a['kosong'] ?? 'Belum ada catatan pada bulan ini.' }}</span>
                        @elseif ($a['status'] === 'tercapai')
                            Tercapai
                        @else
                            Belum tercapai
                        @endif
                    </p>
                </div>
            @endforeach
        </div>
        <p class="border-t border-garis px-5 py-2 text-xs text-slate-600">Hanya persentase di bawah target yang berwarna. Target diatur admin di menu Target &amp; Patokan.</p>
    </section>

    {{-- Inti dashboard: hal yang butuh keputusan kepala sekolah --}}
    <section class="kartu" aria-labelledby="judul-tindakan">
        <div class="border-b border-garis px-4 py-3">
            <h2 id="judul-tindakan" class="font-extrabold">Perlu tindakan Bapak/Ibu{{ $jumlahTindakan ? ' ('.$jumlahTindakan.')' : '' }}</h2>
            <p class="text-xs text-slate-600">Murid yang belum berubah setelah 4 minggu pembinaan dan pelanggaran berat 7 hari terakhir.</p>
        </div>
        @if ($jumlahTindakan)
            <ul class="divide-y divide-garis text-sm">
                @foreach ($pelanggaranBerat as $k)
                    <li class="flex flex-wrap items-start justify-between gap-2 px-4 py-3">
                        <div>
                            <p class="font-bold">{{ $k->murid->nama }} <span class="font-semibold text-slate-600">· Kelas {{ $k->kelas->nama }}</span></p>
                            <p class="text-slate-700">{{ $k->namaPelanggaran() }} · pembinaan: {{ $k->namaTindakan() }} · dicatat {{ TugasAdmin::teksTerakhir($k->tanggal) }}</p>
                        </div>
                        <span class="lencana bg-rose-50 text-rose-800 ring-1 ring-inset ring-rose-200">Pelanggaran berat</span>
                    </li>
                @endforeach
                @foreach ($belumBerubah as $k)
                    <li class="flex flex-wrap items-start justify-between gap-2 px-4 py-3">
                        <div>
                            <p class="font-bold">{{ $k->murid->nama }} <span class="font-semibold text-slate-600">· Kelas {{ $k->kelas->nama }}</span></p>
                            <p class="text-slate-700">{{ $k->namaPelanggaran() }} · pembinaan: {{ $k->namaTindakan() }} · sejak {{ $k->tanggal->format('d/m/Y') }}</p>
                        </div>
                        <span class="lencana bg-amber-50 text-amber-900 ring-1 ring-inset ring-amber-200">Belum berubah 4 minggu</span>
                    </li>
                @endforeach
            </ul>
            <p class="border-t border-garis px-4 py-2 text-xs text-slate-600">Bila perlu, bicarakan dengan wali kelas atau panggil orang tua sesuai tata tertib sekolah.</p>
        @else
            <p class="px-4 py-4 text-sm text-slate-700">Tidak ada yang perlu ditindaklanjuti minggu ini. Murid yang belum berubah setelah 4 minggu pembinaan dan pelanggaran berat akan muncul di sini.</p>
        @endif
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="kartu overflow-hidden" aria-labelledby="judul-pencatatan">
            <div class="border-b border-garis px-4 py-3">
                <h2 id="judul-pencatatan" class="font-extrabold">Pencatatan kelas minggu ini</h2>
                <p class="text-xs text-slate-600">Kegiatan harian yang sudah dicatat wali kelas sejak Senin. Kelas yang tertinggal ada di atas.</p>
            </div>
            @if ($pencatatan->isNotEmpty())
                <table class="w-full text-sm">
                    <thead class="tabel-kepala">
                        <tr>
                            <th class="px-4 py-2">Kelas</th>
                            <th class="px-3 py-2">Terakhir dicatat</th>
                            <th class="px-3 py-2 text-right">Minggu ini</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-garis">
                        @foreach ($pencatatan as $k)
                            @php $tertinggal = $k['jumlah_murid'] > 0 && $k['hari_terlewat'] >= 2; @endphp
                            <tr>
                                <td class="px-4 py-2">
                                    <span class="font-bold">{{ $k['kelas'] }}</span>
                                    <span class="block text-xs text-slate-600">{{ $k['wali'] ?? 'Belum ada wali kelas' }}</span>
                                </td>
                                <td class="px-3 py-2">
                                    <span @class(['rounded-md px-1.5 py-0.5', 'bg-amber-50 font-bold text-amber-900 ring-1 ring-inset ring-amber-200' => $tertinggal, 'text-slate-700' => ! $tertinggal])>{{ $k['jumlah_murid'] ? TugasAdmin::teksTerakhir($k['terakhir']) : 'Belum ada murid' }}</span>
                                </td>
                                <td class="px-3 py-2 text-right font-bold tabular-nums">{{ $k['hari_minggu_ini'] ? $k['minggu_ini'].'/'.$k['hari_minggu_ini'].' hari' : '–' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if (! $pencatatan[0]['hari_minggu_ini'])
                    <p class="border-t border-garis px-4 py-2 text-xs text-slate-600">Belum ada hari kegiatan minggu ini (akhir pekan atau hari libur).</p>
                @endif
            @else
                <p class="px-4 py-4 text-sm text-slate-700">Belum ada kelas pada tahun ajaran aktif. Admin dapat menambahkannya di Halaman Admin, menu Kelas.</p>
            @endif
        </section>

        <div class="space-y-6">
            <section class="kartu" aria-labelledby="judul-kelas">
                <div class="border-b border-garis px-4 py-3">
                    <h2 id="judul-kelas" class="font-extrabold">Kelas yang paling butuh perhatian</h2>
                    <p class="text-xs text-slate-600">{{ $label }}, diurutkan dari yang paling jauh di bawah target.</p>
                </div>
                @if ($kelasPerhatian)
                    <ul class="divide-y divide-garis text-sm">
                        @foreach ($kelasPerhatian as $r)
                            <li class="px-4 py-2.5">
                                <a href="{{ route('monev.kelas', ['kelas' => $r['kelas']->id] + $parameter) }}" wire:navigate class="font-bold underline decoration-slate-300 underline-offset-4 hover:decoration-slate-700">Kelas {{ $r['kelas']->nama }}</a>
                                <p class="text-slate-700">{{ ucfirst(implode(', ', $r['alasan'])) }}</p>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="px-4 py-4 text-sm text-slate-700">Semua kelas sudah mencapai target bulan ini, atau belum ada data kegiatan.</p>
                @endif
            </section>

            <section class="kartu" aria-labelledby="judul-kabar">
                <div class="border-b border-garis px-4 py-3">
                    <h2 id="judul-kabar" class="font-extrabold">Kabar baik</h2>
                    <p class="text-xs text-slate-600">Bahan untuk memberi apresiasi kepada murid dan guru.</p>
                </div>
                <ul class="divide-y divide-garis text-sm">
                    @if ($kelasNaik)
                        <li class="px-4 py-2.5">
                            <p class="font-bold">Kelas {{ $kelasNaik['kelas']->nama }} paling meningkat</p>
                            <p class="text-slate-700">Murid aktif membaca naik {{ Rekap::persenTeks($kelasNaik['naik'], false) }} poin dibanding {{ $labelLalu }}, kini {{ Rekap::persenTeks($kelasNaik['persen']) }}.</p>
                        </li>
                    @endif
                    @foreach ($karya as $k)
                        <li class="px-4 py-2.5">
                            <p class="font-bold">{{ $k->judul }}</p>
                            <p class="text-slate-700">{{ $k->namaJenis() }} · {{ $k->namaPemilik() ?: 'Kelas '.$k->kelas->nama }} · Kelas {{ $k->kelas->nama }} · {{ TugasAdmin::teksTerakhir($k->tanggal) }}</p>
                        </li>
                    @endforeach
                    @if (! $kelasNaik && $karya->isEmpty())
                        <li class="px-4 py-4 text-slate-700">Belum ada karya literasi yang dicatat. Karya murid dan guru akan muncul di sini setelah guru mencatatnya.</li>
                    @endif
                </ul>
            </section>
        </div>
    </div>

    <section aria-label="Pintasan" class="flex flex-wrap gap-2">
        <a href="{{ route('monev.cetak', $parameter) }}" target="_blank" class="tombol-kedua"><x-heroicon-o-printer class="h-4 w-4" /> Cetak ringkasan Monev</a>
        <a href="{{ route('monev', $parameter) }}#judul-unduh" class="tombol-kedua"><x-heroicon-o-arrow-down-tray class="h-4 w-4" /> Unduh laporan baku</a>
        <a href="{{ route('guru.rekap') }}" wire:navigate class="tombol-kedua"><x-heroicon-o-table-cells class="h-4 w-4" /> Rekap per kelas</a>
    </section>
</div>
