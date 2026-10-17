<table class="w-full text-sm">
    <thead class="bg-orange-50/60 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
        <tr>
            <th class="px-4 py-3">No</th>
            <th class="px-3 py-3">Nama Murid</th>
            <th class="px-3 py-3">Kelas</th>
            <th class="px-3 py-3 text-center">Kehadiran Membaca</th>
            <th class="px-3 py-3 text-center">Konsisten</th>
            <th class="px-3 py-3 text-center">Kelengkapan Jurnal</th>
            <th class="px-3 py-3 text-center">Rata-rata</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-orange-50">
        @forelse ($rekap['murid'] as $i => $b)
            <tr>
                <td class="px-4 py-2 text-slate-400">{{ $i + 1 }}</td>
                <td class="px-3 py-2 font-bold">{{ $b['murid']->nama }}</td>
                <td class="px-3 py-2">{{ $this->kelas->nama }}</td>
                <x-sel-rekap :jumlah="$b['hadir']" :n="$rekap['n']" :persen="$b['hadir_persen']" />
                <x-sel-rekap :jumlah="$b['konsisten']" :n="$rekap['n']" :persen="$b['konsisten_persen']" />
                <x-sel-rekap :jumlah="$b['jurnal']" :n="$rekap['n']" :persen="$b['jurnal_persen']" />
                <td class="px-3 py-2 text-center"><x-lencana-kategori :persen="$b['budaya_persen']" /></td>
            </tr>
        @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada murid.</td></tr>
        @endforelse
    </tbody>
</table>
