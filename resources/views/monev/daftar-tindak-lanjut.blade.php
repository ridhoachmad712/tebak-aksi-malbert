{{-- Daftar kasus pembinaan. Butuh: $kasus, $tampilKelas, opsional $kosong --}}
@if ($kasus->isNotEmpty())
    <ul class="divide-y divide-garis text-sm">
        @foreach ($kasus as $k)
            <li class="px-4 py-2.5">
                <p class="font-bold">{{ $k->murid->nama }}@if ($tampilKelas) <span class="font-semibold text-slate-600">· Kelas {{ $k->kelas->nama }}</span>@endif</p>
                <p class="text-slate-700">{{ $k->namaPelanggaran() }} · pembinaan: {{ $k->namaTindakan() }} · sejak {{ $k->tanggal->format('d/m/Y') }}</p>
            </li>
        @endforeach
    </ul>
@else
    <p class="px-4 py-3 text-sm text-slate-700">{{ $kosong ?? 'Tidak ada murid yang perlu tindak lanjut lanjutan. Murid binaan yang belum berubah setelah 4 minggu akan muncul di sini.' }}</p>
@endif
