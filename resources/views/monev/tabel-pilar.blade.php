{{-- Indikator dikelompokkan per pilar program. Butuh: $pilar, $labelLalu --}}
@use('App\Services\Rekap')

<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="tabel-kepala">
            <tr>
                <th class="px-4 py-2.5">Indikator</th>
                <th class="px-3 py-2.5 text-right">Capaian</th>
                <th class="px-3 py-2.5 text-right">Target</th>
                <th class="px-3 py-2.5">Selisih</th>
                <th class="px-3 py-2.5">{{ $labelLalu ? 'Dibanding '.$labelLalu : 'Dibanding periode lalu' }}</th>
                <th class="px-3 py-2.5">Dasar perhitungan</th>
            </tr>
        </thead>
        @foreach ($pilar as $namaPilar => $baris)
            <tbody class="divide-y divide-garis border-t border-garis">
                <tr class="bg-slate-50">
                    <th colspan="6" scope="colgroup" class="px-4 py-1.5 text-left text-xs font-extrabold uppercase tracking-wide text-slate-600">{{ $namaPilar }}</th>
                </tr>
                @foreach ($baris as $b)
                    <tr>
                        <td class="px-4 py-2.5 font-bold">{{ $b['judul'] }}</td>
                        <td class="px-3 py-2.5 text-right text-base">
                            @if ($b['status'] === 'belum_cukup')
                                <span class="font-bold tabular-nums text-slate-500">{{ Rekap::persenTeks($b['nilai']) }}</span>
                            @else
                                <x-nilai-target :nilai="$b['nilai']" :target="$b['target']" />
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-2.5 text-right tabular-nums text-slate-700">≥ {{ Rekap::persenTeks((float) $b['target']) }}</td>
                        <td class="px-3 py-2.5 text-slate-700">
                            @switch($b['status'])
                                @case('tercapai') Tercapai @break
                                @case('kurang') <span class="font-semibold">kurang {{ Rekap::persenTeks($b['selisih'], false) }} poin</span> @break
                                @case('belum_cukup') Data belum cukup untuk disimpulkan @break
                                @default Belum ada data
                            @endswitch
                        </td>
                        <td class="whitespace-nowrap px-3 py-2.5 tabular-nums text-slate-700">
                            @if ($b['tren'] === null)
                                <span class="text-slate-500">–</span>
                            @elseif (abs($b['tren']) < 0.1)
                                tetap
                            @else
                                {{ $b['tren'] > 0 ? 'naik' : 'turun' }} {{ Rekap::persenTeks(abs($b['tren']), false) }} poin
                            @endif
                        </td>
                        <td class="px-3 py-2.5 text-slate-700">{{ $b['keterangan'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        @endforeach
    </table>
</div>
<p class="border-t border-garis px-4 py-2 text-xs text-slate-600">
    Warna hanya muncul pada capaian di bawah target: kuning bila kurang paling banyak {{ \App\Services\RingkasanMonev::SELISIH_RINGAN }} poin, merah bila lebih jauh.
    Keberhasilan pembinaan baru disimpulkan bila ada minimal 5 murid binaan. Target dapat diubah admin di menu Pengaturan.
</p>
