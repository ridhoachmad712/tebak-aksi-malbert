@use('App\Models\KasusPembinaan')

<table class="w-full text-sm">
    <thead class="tabel-kepala">
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
    <tbody class="divide-y divide-garis">
        @forelse ($kasus as $i => $k)
            @php $p = $k->pemantauan->keyBy('minggu'); @endphp
            <tr>
                <td class="px-4 py-2 tabular-nums text-slate-600">{{ $i + 1 }}</td>
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
                    <td class="px-2 py-2 text-center"><x-perkembangan :tingkat="$p[$m]->tingkat ?? null" ringkas /></td>
                @endforeach
                <td class="min-w-40 px-3 py-2">{{ $k->namaPelanggaran() }} <span class="text-xs text-slate-600">({{ strtolower(KasusPembinaan::TINGKAT[$k->tingkat]) }})</span></td>
                <td class="min-w-36 px-3 py-2">{{ $k->namaTindakan() }}</td>
                <td class="whitespace-nowrap px-3 py-2 font-bold">{{ $k->labelStatus() }}</td>
            </tr>
        @empty
            <tr><td colspan="11" class="px-4 py-8 text-center text-slate-600">Tidak ada kasus pembinaan yang dicatat pada bulan ini.</td></tr>
        @endforelse
    </tbody>
</table>
<p class="border-t border-garis px-4 py-2 text-xs text-slate-600">Belum = belum berubah · Mulai = mulai berubah · Positif = berubah positif · Konsisten = konsisten positif · – = belum dipantau</p>
