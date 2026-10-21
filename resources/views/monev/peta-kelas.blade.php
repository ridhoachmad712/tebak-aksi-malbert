{{-- Peta kelas: setiap kelas dibanding target. Butuh: $indikator, $parameter --}}
@use('App\Services\Rekap')
@php
    $rataRataKelas = [];
    foreach (['jumlah', 'persen', 'budaya', 'literasi', 'kasus_total', 'kasus_aktif', 'kasus_selesai'] as $kolomRataRata) {
        $rataRataKelas[$kolomRataRata] = Rekap::rerata(collect($indikator['per_kelas'])->pluck($kolomRataRata)->all());
    }
@endphp

<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="tabel-kepala">
            <tr>
                <th class="px-4 py-2.5">Kelas</th>
                <th class="px-3 py-2.5">Wali kelas</th>
                <th class="px-3 py-2.5 text-right">Murid</th>
                <th class="px-3 py-2.5 text-center">Murid aktif<span class="block text-xs font-semibold normal-case text-slate-500">target {{ Rekap::persenTeks((float) $indikator['murid_aktif']['target']) }}</span></th>
                <th class="px-3 py-2.5 text-center">Budaya Membaca<span class="block text-xs font-semibold normal-case text-slate-500">target {{ Rekap::persenTeks((float) $indikator['budaya_membaca']['target']) }}</span></th>
                <th class="px-3 py-2.5 text-center">Kemampuan Literasi<span class="block text-xs font-semibold normal-case text-slate-500">target {{ Rekap::persenTeks((float) $indikator['kemampuan_literasi']['target']) }}</span></th>
                <th class="px-3 py-2.5 text-center">Total kasus</th>
                <th class="px-3 py-2.5 text-center">Masih dibina</th>
                <th class="px-3 py-2.5 text-center">Selesai</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-garis">
            @forelse ($indikator['per_kelas'] as $r)
                <tr>
                    <td class="px-4 py-2.5 font-bold">
                        <a href="{{ route('monev.kelas', ['kelas' => $r['kelas']->id] + $parameter) }}" wire:navigate class="underline decoration-slate-300 underline-offset-4 hover:decoration-slate-700">Kelas {{ $r['kelas']->nama }}</a>
                    </td>
                    <td class="px-3 py-2.5 text-slate-700">{{ $r['kelas']->waliKelas?->name ?? 'Belum ada' }}</td>
                    <td class="px-3 py-2.5 text-right tabular-nums">{{ $r['jumlah'] }}</td>
                    <td class="px-3 py-2.5 text-center"><x-nilai-target :nilai="$r['persen']" :target="$indikator['murid_aktif']['target']" /></td>
                    <td class="px-3 py-2.5 text-center"><x-nilai-target :nilai="$r['budaya']" :target="$indikator['budaya_membaca']['target']" /></td>
                    <td class="px-3 py-2.5 text-center"><x-nilai-target :nilai="$r['literasi']" :target="$indikator['kemampuan_literasi']['target']" /></td>
                    <td class="px-3 py-2.5 text-center tabular-nums">{{ $r['kasus_total'] }}</td>
                    <td class="px-3 py-2.5 text-center tabular-nums {{ $r['kasus_aktif'] ? 'font-bold' : 'text-slate-500' }}">{{ $r['kasus_aktif'] }}</td>
                    <td class="px-3 py-2.5 text-center tabular-nums">{{ $r['kasus_selesai'] }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="px-4 py-5 text-slate-700">Belum ada kelas pada tahun ajaran aktif. Admin dapat menambahkannya di Halaman Admin, menu Kelas.</td></tr>
            @endforelse
        </tbody>
        @if (count($indikator['per_kelas']))
            <tfoot class="border-t border-garis bg-krem font-bold">
                <tr>
                    <th scope="row" colspan="2" class="px-4 py-2.5 text-left">Total</th>
                    <td class="px-3 py-2.5 text-center tabular-nums">{{ collect($indikator['per_kelas'])->sum('jumlah') }}</td>
                    <td colspan="3" class="px-3 py-2.5 text-center tabular-nums">-</td>
                    <td class="px-3 py-2.5 text-center tabular-nums">{{ collect($indikator['per_kelas'])->sum('kasus_total') }}</td>
                    <td class="px-3 py-2.5 text-center tabular-nums">{{ collect($indikator['per_kelas'])->sum('kasus_aktif') }}</td>
                    <td class="px-3 py-2.5 text-center tabular-nums">{{ collect($indikator['per_kelas'])->sum('kasus_selesai') }}</td>
                </tr>
                <tr>
                    <th scope="row" colspan="2" class="px-4 py-2.5 text-left">Rata-rata per kelas</th>
                    <td class="px-3 py-2.5 text-right tabular-nums">{{ Rekap::persenTeks($rataRataKelas['jumlah'], false) }}</td>
                    <td class="px-3 py-2.5 text-center"><x-nilai-target :nilai="$rataRataKelas['persen']" :target="$indikator['murid_aktif']['target']" /></td>
                    <td class="px-3 py-2.5 text-center"><x-nilai-target :nilai="$rataRataKelas['budaya']" :target="$indikator['budaya_membaca']['target']" /></td>
                    <td class="px-3 py-2.5 text-center"><x-nilai-target :nilai="$rataRataKelas['literasi']" :target="$indikator['kemampuan_literasi']['target']" /></td>
                    <td class="px-3 py-2.5 text-center tabular-nums">{{ Rekap::persenTeks($rataRataKelas['kasus_total'], false) }}</td>
                    <td class="px-3 py-2.5 text-center tabular-nums">{{ Rekap::persenTeks($rataRataKelas['kasus_aktif'], false) }}</td>
                    <td class="px-3 py-2.5 text-center tabular-nums">{{ Rekap::persenTeks($rataRataKelas['kasus_selesai'], false) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
<p class="border-t border-garis px-4 py-2 text-xs text-slate-600">Hanya sel di bawah target yang berwarna. Budaya Membaca dan Kemampuan Literasi menunjukkan persentase murid yang rata-rata indikatornya mencapai batas murid aktif. Kasus dihitung berdasarkan tanggal kejadian dalam periode terpilih. Masih dibina dan Selesai menunjukkan status terkini, bukan status pada akhir periode. Rata-rata dihitung antar kelas; persentase yang belum tersedia tidak ikut dihitung. Pilih nama kelas untuk melihat murid yang perlu perhatian dan kasus pembinaannya.</p>
