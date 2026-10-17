{{-- Peta kelas: setiap kelas dibanding target. Butuh: $indikator, $parameter --}}
@use('App\Services\Rekap')

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
                <th class="px-3 py-2.5 text-center">Kasus dalam pembinaan</th>
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
                    <td class="px-3 py-2.5 text-center tabular-nums {{ $r['kasus_aktif'] ? 'font-bold' : 'text-slate-500' }}">{{ $r['kasus_aktif'] }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-5 text-slate-700">Belum ada kelas pada tahun ajaran aktif. Admin dapat menambahkannya di Halaman Admin, menu Kelas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<p class="border-t border-garis px-4 py-2 text-xs text-slate-600">Hanya sel di bawah target yang berwarna. Pilih nama kelas untuk melihat murid yang perlu perhatian dan kasus pembinaannya.</p>
