<table class="w-full text-sm">
    <thead class="tabel-kepala">
        <tr>
            <th class="px-4 py-3">No</th>
            <th class="px-3 py-3">Nama Murid</th>
            <th class="px-3 py-3">Kelas</th>
            <th class="px-3 py-3 text-center">Memahami</th>
            <th class="px-3 py-3 text-center">Merangkum</th>
            <th class="px-3 py-3 text-center">Menyampaikan Informasi</th>
            <th class="px-3 py-3 text-center">Rata-rata</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-garis">
        @forelse ($rekap['murid'] as $i => $b)
            <tr>
                <td class="px-4 py-2 tabular-nums text-slate-600">{{ $i + 1 }}</td>
                <td class="px-3 py-2 font-bold">{{ $b['murid']->nama }}</td>
                <td class="px-3 py-2">{{ $this->kelas->nama }}</td>
                <x-sel-rekap :jumlah="$b['memahami']" :n="$rekap['n']" :persen="$b['memahami_persen']" />
                <x-sel-rekap :jumlah="$b['merangkum']" :n="$rekap['n']" :persen="$b['merangkum_persen']" />
                <x-sel-rekap :jumlah="$b['menyampaikan']" :n="$rekap['n']" :persen="$b['menyampaikan_persen']" />
                <td class="px-3 py-2 text-center"><x-lencana-kategori :persen="$b['literasi_persen']" /></td>
            </tr>
        @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-slate-600">Belum ada murid.</td></tr>
        @endforelse
    </tbody>
</table>
