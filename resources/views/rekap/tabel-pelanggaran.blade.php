@use('App\Models\KasusPembinaan')

<table class="w-full text-sm">
    <thead class="bg-orange-50/60 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
        <tr>
            <th class="px-4 py-3">No</th>
            <th class="px-3 py-3">Nama Murid</th>
            <th class="px-3 py-3">Kelas</th>
            <th class="px-3 py-3">Kondisi Awal</th>
            @foreach (range(1, 4) as $m)
                <th class="px-2 py-3 text-center">Minggu {{ $m }}</th>
            @endforeach
            <th class="px-3 py-3">Jenis Pelanggaran</th>
            <th class="px-3 py-3">Tindakan Pembinaan</th>
            <th class="px-3 py-3">Status</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-orange-50">
        @forelse ($kasus as $i => $k)
            @php $p = $k->pemantauan->keyBy('minggu'); @endphp
            <tr>
                <td class="px-4 py-2 text-slate-400">{{ $i + 1 }}</td>
                <td class="px-3 py-2 font-bold">
                    @if (auth()->user()->bolehAksesKelas($k->kelas_id) && ! auth()->user()->isKepsek())
                        <a href="{{ route('guru.pembinaan.detail', $k) }}" wire:navigate class="hover:text-brand-700">{{ $k->murid->nama }}</a>
                    @else
                        {{ $k->murid->nama }}
                    @endif
                </td>
                <td class="px-3 py-2">{{ $k->kelas->nama }}</td>
                <td class="min-w-40 px-3 py-2">{{ $k->kondisi_awal }}</td>
                @foreach (range(1, 4) as $m)
                    <td class="px-2 py-2 text-center text-lg" title="{{ isset($p[$m]) ? KasusPembinaan::PERKEMBANGAN[$p[$m]->tingkat]['label'] : 'Belum dipantau' }}">
                        {{ isset($p[$m]) ? KasusPembinaan::PERKEMBANGAN[$p[$m]->tingkat]['ikon'] : '·' }}
                    </td>
                @endforeach
                <td class="min-w-40 px-3 py-2">{{ $k->namaPelanggaran() }} <span class="text-xs text-slate-400">({{ KasusPembinaan::TINGKAT[$k->tingkat] }})</span></td>
                <td class="min-w-36 px-3 py-2">{{ $k->namaTindakan() }}</td>
                <td class="whitespace-nowrap px-3 py-2 font-bold">{{ $k->labelStatus() }}</td>
            </tr>
        @empty
            <tr><td colspan="11" class="px-4 py-8 text-center text-slate-500">Tidak ada kasus pembinaan pada bulan ini. 🌈</td></tr>
        @endforelse
    </tbody>
</table>
<p class="border-t border-orange-50 px-4 py-2 text-xs text-slate-500">🟥 Belum berubah · 🟨 Mulai berubah · 🟩 Berubah positif · ⭐ Konsisten positif</p>
