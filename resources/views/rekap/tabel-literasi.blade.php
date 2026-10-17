<table class="w-full text-sm">
    <thead class="tabel-kepala">
        <tr>
            <th class="px-4 py-3">No</th>
            <th class="px-3 py-3">Nama Murid</th>
            <th class="px-3 py-3 text-center">Memahami</th>
            <th class="px-3 py-3 text-center">Merangkum</th>
            <th class="px-3 py-3 text-center">Menyampaikan Informasi</th>
            <th class="px-3 py-3 text-center">Rata-rata</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-garis">
        @forelse ($murid as $i => $b)
            <tr>
                <td class="px-4 py-2 tabular-nums text-slate-600">{{ $i + 1 }}</td>
                <td class="px-3 py-2 font-bold">{{ $b['murid']->nama }}</td>
                <x-sel-rekap :jumlah="$b['memahami']" :n="$rekap['n']" :persen="$b['memahami_persen']" />
                <x-sel-rekap :jumlah="$b['merangkum']" :n="$rekap['n']" :persen="$b['merangkum_persen']" />
                <x-sel-rekap :jumlah="$b['menyampaikan']" :n="$rekap['n']" :persen="$b['menyampaikan_persen']" />
                <td class="px-3 py-2 text-center"><x-lencana-kategori :persen="$b['literasi_persen']" class="font-extrabold" /></td>
            </tr>
        @empty
            @include('rekap.kosong', ['kolom' => 6])
        @endforelse
    </tbody>
    @if ($rekap['murid']->isNotEmpty())
        @include('rekap.baris-rerata', ['kolom' => ['memahami', 'merangkum', 'menyampaikan', 'literasi']])
    @endif
</table>
