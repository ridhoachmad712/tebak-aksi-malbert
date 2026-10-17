<table class="w-full text-sm">
    <thead class="tabel-kepala">
        <tr>
            <th class="px-4 py-3">No</th>
            <th class="px-3 py-3">Nama Murid/Guru</th>
            <th class="px-3 py-3">Kelas</th>
            <th class="px-3 py-3 text-center">Memanfaatkan Teras Baca</th>
            <th class="px-3 py-3 text-center">Karya Literasi</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-garis">
        @php $no = 0; @endphp
        @foreach ($guru['guru'] as $g)
            <tr class="bg-toska-50">
                <td class="px-4 py-2 tabular-nums text-slate-600">{{ ++$no }}</td>
                <td class="px-3 py-2 font-bold">{{ $g['guru']->name }} <span class="text-sm font-semibold text-slate-600">(guru)</span></td>
                <td class="px-3 py-2">{{ $this->kelas->nama }}</td>
                <x-sel-rekap :jumlah="$g['hari']" :n="$guru['n']" :persen="$g['persen']" />
                <td class="px-3 py-2 text-center font-bold">{{ $g['karya'] }} karya</td>
            </tr>
        @endforeach
        @foreach ($rekap['murid'] as $b)
            <tr>
                <td class="px-4 py-2 tabular-nums text-slate-600">{{ ++$no }}</td>
                <td class="px-3 py-2 font-bold">{{ $b['murid']->nama }}</td>
                <td class="px-3 py-2">{{ $this->kelas->nama }}</td>
                <x-sel-rekap :jumlah="$b['hadir']" :n="$rekap['n']" :persen="$b['hadir_persen']" />
                <td class="px-3 py-2 text-center font-bold">{{ $b['karya'] }} karya</td>
            </tr>
        @endforeach
    </tbody>
</table>
<p class="border-t border-garis px-4 py-2 text-xs text-slate-600">Memanfaatkan Teras Baca: murid dihitung dari centang Hadir; guru dihitung dari hari ia mengisi Kegiatan Harian kelas ini.</p>
