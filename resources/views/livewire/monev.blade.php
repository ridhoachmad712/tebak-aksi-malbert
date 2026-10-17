@php
    $kartu = [
        'murid_aktif' => ['📚', 'Murid aktif memanfaatkan Teras Baca'],
        'guru_aktif' => ['👩‍🏫', 'Guru aktif memanfaatkan Teras Baca'],
        'keberhasilan_pembinaan' => ['🌱', 'Keberhasilan pembinaan'],
        'penurunan_pelanggaran' => ['📉', 'Penurunan pelanggaran'],
        'budaya_membaca' => ['📖', 'Rata-rata Budaya Membaca'],
        'kemampuan_literasi' => ['💡', 'Rata-rata Kemampuan Literasi'],
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="judul-halaman">Monev TEBAK AKSI</h1>
            <p class="text-sm text-slate-500">Laporan pelaksanaan dan capaian program · {{ $label }} · {{ $hari }} hari kegiatan</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <select wire:model.live="periode" class="isian w-auto py-2 pr-9 font-bold">
                <option value="bulan">Per bulan</option>
                <option value="semester">Per semester</option>
            </select>
            @if ($periode === 'semester')
                <select wire:model.live="semester" class="isian w-auto py-2 pr-9 font-bold">
                    <option value="1">Semester Ganjil</option>
                    <option value="2">Semester Genap</option>
                </select>
            @else
                <input type="month" wire:model.live="bulan" max="{{ today()->format('Y-m') }}" class="isian w-auto py-2 font-bold">
            @endif
            <select wire:model.live="kelas" class="isian w-auto py-2 pr-9 font-bold">
                <option value="">Semua kelas</option>
                @foreach ($daftarKelas as $k)
                    <option value="{{ $k->id }}">Kelas {{ $k->nama }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @unless ($ta)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 font-semibold text-amber-900">Tahun ajaran aktif belum diatur. Admin dapat mengaturnya di Halaman Admin → Tahun Ajaran.</div>
    @endunless

    {{-- Indikator keberhasilan --}}
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" wire:loading.class="opacity-60">
        @foreach ($kartu as $kunci => [$ikon, $judul])
            @php
                $d = $indikator[$kunci];
                $tercapai = $d['nilai'] !== null && $d['nilai'] >= $d['target'];
                $lebar = $d['nilai'] === null ? 0 : max(0, min(100, $d['nilai']));
            @endphp
            <div class="kartu p-5">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-sm font-bold text-slate-600">{{ $ikon }} {{ $judul }}</p>
                    @if ($d['nilai'] !== null)
                        <span @class(['lencana shrink-0', 'bg-emerald-100 text-emerald-800' => $tercapai, 'bg-amber-100 text-amber-800' => ! $tercapai])>
                            {{ $tercapai ? 'Tercapai' : 'Belum tercapai' }}
                        </span>
                    @endif
                </div>
                <p class="mt-2 text-4xl font-extrabold tracking-tight">{{ $d['nilai'] !== null ? $d['nilai'].'%' : '–' }}</p>
                <div class="relative mt-3 h-2.5 rounded-full bg-slate-100">
                    <div @class(['h-2.5 rounded-full', 'bg-emerald-500' => $tercapai, 'bg-brand-400' => ! $tercapai]) style="width: {{ $lebar }}%"></div>
                    <div class="absolute -top-1 h-4.5 w-0.5 bg-slate-700" style="left: {{ min(100, $d['target']) }}%" title="Target"></div>
                </div>
                <p class="mt-2 text-xs font-semibold text-slate-500">Target ≥ {{ (float) $d['target'] }}% · {{ $d['keterangan'] }}</p>
            </div>
        @endforeach
    </section>

    {{-- Capaian per kelas --}}
    <section class="kartu overflow-hidden">
        <div class="border-b border-orange-100 px-4 py-3">
            <h2 class="font-extrabold">Capaian per kelas</h2>
            <p class="text-xs text-slate-500">Murid aktif = kehadiran membaca ≥ {{ (int) \App\Models\Pengaturan::angka('batas_murid_aktif') }}%</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-orange-50/60 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Kelas</th>
                        <th class="px-3 py-3 text-center">Jumlah Murid</th>
                        <th class="px-3 py-3 text-center">Aktif</th>
                        <th class="px-3 py-3 text-center">Belum Aktif</th>
                        <th class="px-3 py-3 text-center">% Aktif</th>
                        <th class="px-3 py-3 text-center">Budaya Membaca</th>
                        <th class="px-3 py-3 text-center">Kemampuan Literasi</th>
                        <th class="px-3 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-orange-50">
                    @foreach ($indikator['per_kelas'] as $r)
                        <tr>
                            <td class="px-4 py-2 font-extrabold">Kelas {{ $r['kelas']->nama }}</td>
                            <td class="px-3 py-2 text-center">{{ $r['jumlah'] }}</td>
                            <td class="px-3 py-2 text-center font-bold text-emerald-700">{{ $r['aktif'] }}</td>
                            <td class="px-3 py-2 text-center font-bold text-rose-700">{{ $r['belum'] }}</td>
                            <td class="px-3 py-2 text-center"><x-lencana-kategori :persen="$r['persen']" /></td>
                            <td class="px-3 py-2 text-center"><x-lencana-kategori :persen="$r['budaya']" /></td>
                            <td class="px-3 py-2 text-center"><x-lencana-kategori :persen="$r['literasi']" /></td>
                            <td class="px-3 py-2 text-right">
                                <a href="{{ route('guru.rekap', ['kelas' => $r['kelas']->id, 'bulan' => $bulan]) }}" wire:navigate class="tautan text-xs">Rekap →</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Aspek karakter --}}
        <section class="kartu overflow-hidden">
            <div class="border-b border-orange-100 px-4 py-3">
                <h2 class="font-extrabold">Aspek karakter yang perlu dibina</h2>
                <p class="text-xs text-slate-500">Jumlah kasus pembinaan per aspek karakter</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-orange-50/60 text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Kelas</th>
                            @foreach ($indikator['aspek']['aspek'] as $a)
                                <th class="px-2 py-3 text-center normal-case">{{ $a }}</th>
                            @endforeach
                            <th class="px-3 py-3 text-left">Paling perlu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-50">
                        @foreach ($indikator['aspek']['kelas'] as $r)
                            <tr>
                                <td class="px-4 py-2 font-bold">{{ $r['kelas']->nama }}</td>
                                @foreach ($indikator['aspek']['aspek'] as $a)
                                    @php $j = $r['hitung'][$a] ?? 0; @endphp
                                    <td class="px-2 py-2 text-center">
                                        <span @class(['inline-grid h-7 min-w-7 place-items-center rounded-lg px-1 font-bold', 'bg-rose-100 text-rose-700' => $j >= 2, 'bg-amber-50 text-amber-700' => $j === 1, 'text-slate-300' => $j === 0])>{{ $j }}</span>
                                    </td>
                                @endforeach
                                <td class="px-3 py-2 font-bold text-rose-700">{{ $r['tertinggi'] ?? '–' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Tren bulanan --}}
        <section class="kartu p-5">
            <h2 class="font-extrabold">Tren tahun ajaran ini</h2>
            <p class="text-xs text-slate-500">Batang = rata-rata kehadiran membaca · angka merah = jumlah kasus baru</p>
            <div class="mt-4 flex h-48 items-end gap-3">
                @forelse ($tren as $t)
                    <div class="flex flex-1 flex-col items-center gap-1">
                        <span class="text-xs font-bold text-slate-600">{{ $t['hadir'] !== null ? round($t['hadir']).'%' : '–' }}</span>
                        <div class="w-full max-w-12 rounded-t-lg bg-gradient-to-t from-brand-500 to-orange-300" style="height: {{ (int) (($t['hadir'] ?? 0) * 1.3) }}px"></div>
                        <span class="text-xs font-bold text-rose-600">{{ $t['kasus'] }}</span>
                        <span class="text-xs font-semibold text-slate-500">{{ $t['label'] }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada data.</p>
                @endforelse
            </div>
        </section>
    </div>

    {{-- Tindak lanjut --}}
    <section class="kartu overflow-hidden">
        <div class="border-b border-orange-100 px-4 py-3">
            <h2 class="font-extrabold">🟥 Murid yang perlu tindak lanjut</h2>
            <p class="text-xs text-slate-500">Masa pemantauan selesai, tetapi status masih "Belum berubah"</p>
        </div>
        <ul class="divide-y divide-orange-50">
            @forelse ($indikator['tindak_lanjut'] as $k)
                <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                    <span><b>{{ $k->murid->nama }}</b> · Kelas {{ $k->kelas->nama }} · {{ $k->namaPelanggaran() }}</span>
                    <span class="text-slate-500">Dicatat {{ $k->tanggal->format('d/m/Y') }} · {{ $k->namaTindakan() }}</span>
                </li>
            @empty
                <li class="px-4 py-6 text-center text-sm text-slate-500">Tidak ada murid yang perlu tindak lanjut. 🌈</li>
            @endforelse
        </ul>
    </section>

    {{-- Unduh laporan baku --}}
    <section class="kartu p-5">
        <h2 class="font-extrabold">Unduh laporan baku {{ $periode === 'bulan' ? $label : '(per bulan)' }}</h2>
        <p class="text-sm text-slate-500">Seluruh kelas{{ $kelas ? ' (atau kelas terpilih)' : '' }}, siap dicetak sebagai acuan formulir evaluasi tim monev.</p>
        <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (\App\Livewire\Guru\RekapKelas::LAPORAN as $kunci => $judulLap)
                <div class="rounded-xl border border-slate-200 p-3">
                    <p class="font-bold">{{ $judulLap }}</p>
                    <div class="mt-2 flex gap-2">
                        <a href="{{ route('rekap.unduh', ['kelas' => $kelas ?: null, 'bulan' => $bulan, 'laporan' => $kunci, 'format' => 'pdf']) }}" target="_blank" class="tombol-kedua px-3 py-1.5 text-xs">PDF</a>
                        <a href="{{ route('rekap.unduh', ['kelas' => $kelas ?: null, 'bulan' => $bulan, 'laporan' => $kunci, 'format' => 'xlsx']) }}" class="tombol-kedua px-3 py-1.5 text-xs">Excel</a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>
