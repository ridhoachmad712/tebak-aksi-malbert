@php
    $daftarIndikator = [
        'murid_aktif' => 'Murid aktif memanfaatkan Teras Baca',
        'guru_aktif' => 'Wali kelas aktif mengisi kegiatan harian',
        'budaya_membaca' => 'Rata-rata Budaya Membaca',
        'kemampuan_literasi' => 'Rata-rata Kemampuan Literasi',
        'keberhasilan_pembinaan' => 'Keberhasilan pembinaan',
        'penurunan_pelanggaran' => 'Penurunan pelanggaran per semester',
    ];
    $perluPerhatian = $indikator['kelas_di_bawah_target']->isNotEmpty() || $indikator['tindak_lanjut']->isNotEmpty();
    $hadirMaks = 100;
@endphp

<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="judul-halaman">Monev TEBAK AKSI</h1>
            <p class="teks-bantu">{{ $label }} · {{ $hari }} hari kegiatan{{ $kelas ? '' : ' · seluruh kelas' }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
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
            <select wire:model.live="kelas" aria-label="Kelas" class="isian w-auto pr-9 font-bold">
                <option value="">Semua kelas</option>
                @foreach ($daftarKelas as $k)
                    <option value="{{ $k->id }}">Kelas {{ $k->nama }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @unless ($ta)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 font-semibold text-amber-900">Tahun ajaran aktif belum diatur. Admin dapat mengaturnya di Halaman Admin, menu Tahun Ajaran.</div>
    @endunless

    {{-- Titik fokus: yang perlu ditindaklanjuti kepala sekolah --}}
    <section aria-labelledby="judul-perhatian" wire:loading.class="opacity-60">
        <h2 id="judul-perhatian" class="mb-3 text-lg font-extrabold">Perlu perhatian</h2>
        @if ($perluPerhatian)
            <div class="grid gap-4 lg:grid-cols-2">
                @if ($indikator['kelas_di_bawah_target']->isNotEmpty())
                    <div class="kartu">
                        <h3 class="border-b border-garis px-4 py-3 font-bold">Kelas dengan murid aktif di bawah target {{ (float) $indikator['murid_aktif']['target'] }}%</h3>
                        <ul class="divide-y divide-garis text-sm">
                            @foreach ($indikator['kelas_di_bawah_target'] as $r)
                                <li class="flex items-center justify-between gap-3 px-4 py-2.5">
                                    <a href="{{ route('guru.rekap', ['kelas' => $r['kelas']->id] + ($periode === 'semester' ? ['periode' => 'semester', 'semester' => $semester] : ['bulan' => $bulan])) }}" wire:navigate class="font-bold hover:underline">Kelas {{ $r['kelas']->nama }}</a>
                                    <span class="text-slate-700">{{ $r['aktif'] }} dari {{ $r['jumlah'] }} murid aktif <x-lencana-kategori :persen="$r['persen']" class="ml-1" /></span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if ($indikator['tindak_lanjut']->isNotEmpty())
                    <div class="kartu">
                        <h3 class="border-b border-garis px-4 py-3 font-bold">Murid belum berubah setelah 4 minggu pembinaan</h3>
                        <ul class="divide-y divide-garis text-sm">
                            @foreach ($indikator['tindak_lanjut'] as $k)
                                <li class="px-4 py-2.5">
                                    <p class="font-bold">{{ $k->murid->nama }} <span class="font-semibold text-slate-600">· Kelas {{ $k->kelas->nama }}</span></p>
                                    <p class="text-slate-700">{{ $k->namaPelanggaran() }} · pembinaan: {{ $k->namaTindakan() }} · sejak {{ $k->tanggal->format('d/m/Y') }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @else
            <div class="kartu p-5 text-sm text-slate-700">Pada periode ini semua kelas mencapai target murid aktif dan tidak ada murid yang belum berubah setelah 4 minggu pembinaan.</div>
        @endif
    </section>

    <section class="kartu overflow-hidden" aria-labelledby="judul-indikator" wire:loading.class="opacity-60">
        <h2 id="judul-indikator" class="border-b border-garis px-4 py-3 font-extrabold">Capaian dibanding target</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="tabel-kepala">
                    <tr>
                        <th class="px-4 py-2.5">Indikator</th>
                        <th class="px-3 py-2.5 text-right">Capaian</th>
                        <th class="px-3 py-2.5 text-right">Target</th>
                        <th class="px-3 py-2.5">Status</th>
                        <th class="px-3 py-2.5">Dasar perhitungan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-garis">
                    @foreach ($daftarIndikator as $kunci => $judul)
                        @php
                            $d = $indikator[$kunci];
                            $tercapai = $d['nilai'] !== null && $d['nilai'] >= $d['target'];
                        @endphp
                        <tr>
                            <td class="px-4 py-2.5 font-bold">{{ $judul }}</td>
                            <td class="px-3 py-2.5 text-right text-base font-extrabold tabular-nums">{{ $d['nilai'] !== null ? $d['nilai'].'%' : '–' }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums text-slate-700">≥ {{ (float) $d['target'] }}%</td>
                            <td class="px-3 py-2.5">
                                @if ($d['nilai'] === null)
                                    <span class="text-slate-600">Belum ada data</span>
                                @elseif ($tercapai)
                                    <span class="lencana bg-emerald-50 text-emerald-800 ring-1 ring-inset ring-emerald-200">Tercapai</span>
                                @else
                                    <span class="lencana bg-amber-50 text-amber-900 ring-1 ring-inset ring-amber-200">Belum tercapai</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-slate-700">{{ $d['keterangan'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="border-t border-garis px-4 py-2 text-xs text-slate-600">Target dan batas perhitungan dapat diubah admin di menu Pengaturan Target &amp; Patokan.</p>
    </section>

    <section class="kartu overflow-hidden" aria-labelledby="judul-kelas">
        <h2 id="judul-kelas" class="border-b border-garis px-4 py-3 font-extrabold">Capaian per kelas</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="tabel-kepala">
                    <tr>
                        <th class="px-4 py-2.5">Kelas</th>
                        <th class="px-3 py-2.5 text-right">Murid</th>
                        <th class="px-3 py-2.5 text-right">Aktif</th>
                        <th class="px-3 py-2.5 text-right">Belum aktif</th>
                        <th class="px-3 py-2.5 text-center">% aktif</th>
                        <th class="px-3 py-2.5 text-center">Budaya Membaca</th>
                        <th class="px-3 py-2.5 text-center">Kemampuan Literasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-garis">
                    @foreach ($indikator['per_kelas'] as $r)
                        <tr>
                            <td class="px-4 py-2.5 font-bold">
                                <a href="{{ route('guru.rekap', ['kelas' => $r['kelas']->id] + ($periode === 'semester' ? ['periode' => 'semester', 'semester' => $semester] : ['bulan' => $bulan])) }}" wire:navigate class="hover:underline">Kelas {{ $r['kelas']->nama }}</a>
                            </td>
                            <td class="px-3 py-2.5 text-right tabular-nums">{{ $r['jumlah'] }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums">{{ $r['aktif'] }}</td>
                            <td class="px-3 py-2.5 text-right tabular-nums">{{ $r['belum'] }}</td>
                            <td class="px-3 py-2.5 text-center"><x-lencana-kategori :persen="$r['persen']" /></td>
                            <td class="px-3 py-2.5 text-center"><x-lencana-kategori :persen="$r['budaya']" /></td>
                            <td class="px-3 py-2.5 text-center"><x-lencana-kategori :persen="$r['literasi']" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-garis px-4 py-2"><x-keterangan-kategori /></div>
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="kartu overflow-hidden" aria-labelledby="judul-aspek">
            <div class="border-b border-garis px-4 py-3">
                <h2 id="judul-aspek" class="font-extrabold">Jumlah kasus per aspek karakter</h2>
                <p class="text-xs text-slate-600">Aspek diambil dari jenis pelanggaran yang dicatat guru.</p>
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

        <section class="kartu p-5" aria-labelledby="judul-tren">
            <h2 id="judul-tren" class="font-extrabold">Rata-rata kehadiran membaca per bulan</h2>
            <p class="text-xs text-slate-600">Tahun ajaran {{ $ta?->nama }}{{ $kelas ? ', kelas terpilih' : ', seluruh kelas' }}. Garis putus-putus = target murid aktif.</p>
            @if ($tren)
                <div class="relative mt-4 flex h-44 items-end gap-3 border-b border-slate-300">
                    <div class="pointer-events-none absolute inset-x-0 border-t-2 border-dashed border-slate-400" style="bottom: {{ min(100, $indikator['murid_aktif']['target']) }}%"></div>
                    @foreach ($tren as $t)
                        <div class="flex h-full flex-1 flex-col items-center justify-end gap-1">
                            <span class="text-xs font-bold tabular-nums text-slate-700">{{ $t['hadir'] !== null ? round($t['hadir']).'%' : '–' }}</span>
                            <div class="w-full max-w-12 rounded-t-md bg-brand-700" style="height: {{ max(0, min(100, $t['hadir'] ?? 0)) }}%"></div>
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
    </div>

    <section class="kartu p-5" aria-labelledby="judul-unduh">
        <h2 id="judul-unduh" class="font-extrabold">Unduh laporan baku, {{ $label }}</h2>
        <p class="teks-bantu">{{ $kelas ? 'Kelas terpilih' : 'Seluruh kelas' }}, sesuai format sekolah. Dapat dipakai sebagai acuan formulir evaluasi tim monev.</p>
        <div class="mt-3 divide-y divide-garis rounded-lg border border-garis">
            @foreach (\App\Livewire\Guru\RekapKelas::LAPORAN as $kunci => $judulLap)
                <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-2">
                    <span class="font-bold">{{ $judulLap }}</span>
                    <span class="flex gap-2">
                        <a href="{{ route('rekap.unduh', ['laporan' => $kunci, 'format' => 'pdf'] + $unduh) }}" target="_blank" class="tombol-kedua">PDF</a>
                        <a href="{{ route('rekap.unduh', ['laporan' => $kunci, 'format' => 'xlsx'] + $unduh) }}" class="tombol-kedua">Excel</a>
                    </span>
                </div>
            @endforeach
        </div>
    </section>
</div>
