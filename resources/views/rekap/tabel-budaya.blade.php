<table class="w-full text-sm">
    <thead class="tabel-kepala">
        <tr>
            <th class="px-4 py-3">No</th>
            <th class="px-3 py-3">Nama Murid</th>
            <th class="px-3 py-3 text-center">Kehadiran Membaca</th>
            <th class="px-3 py-3 text-center">Konsisten</th>
            <th class="px-3 py-3 text-center">Kelengkapan Jurnal</th>
            <th class="px-3 py-3 text-center">Rata-rata</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-garis">
        @forelse ($murid as $i => $b)
            <tr>
                <td class="px-4 py-2 tabular-nums text-slate-600">{{ $i + 1 }}</td>
                <td class="px-3 py-2 font-bold">{{ $b['murid']->nama }}</td>
                <x-sel-rekap :jumlah="$b['hadir']" :n="$rekap['n']" :persen="$b['hadir_persen']" />
                <x-sel-rekap :jumlah="$b['konsisten']" :n="$rekap['n']" :persen="$b['konsisten_persen']" />
                <x-sel-rekap :jumlah="$b['jurnal']" :n="$rekap['n']" :persen="$b['jurnal_persen']" />
                <td class="px-3 py-2 text-center"><x-lencana-kategori :persen="$b['budaya_persen']" class="font-extrabold" /></td>
            </tr>
        @empty
            @include('rekap.kosong', ['kolom' => 6])
        @endforelse
    </tbody>
    @if ($rekap['murid']->isNotEmpty())
        @include('rekap.baris-rerata', ['kolom' => ['hadir', 'konsisten', 'jurnal', 'budaya']])
    @endif
</table>
