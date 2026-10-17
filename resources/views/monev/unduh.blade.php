{{-- Unduhan 4 laporan baku. Butuh: $label, $unduh, $keterangan --}}
<section class="kartu p-5" aria-labelledby="judul-unduh">
    <h2 id="judul-unduh" class="font-extrabold">Unduh laporan baku, {{ $label }}</h2>
    <p class="teks-bantu">{{ $keterangan }}, sesuai format sekolah. Dapat dipakai sebagai acuan formulir evaluasi tim monev.</p>
    <div class="mt-3 divide-y divide-garis rounded-lg border border-garis">
        @foreach (\App\Livewire\Guru\RekapKelas::LAPORAN as $kunci => $judulLap)
            <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-2">
                <span class="font-bold">{{ $judulLap }}</span>
                <span class="flex gap-2">
                    <a href="{{ route('rekap.unduh', ['laporan' => $kunci, 'format' => 'pdf'] + $unduh) }}" target="_blank" class="tombol-kedua">PDF</a>
                    <a href="{{ route('rekap.unduh', ['laporan' => $kunci, 'format' => 'xlsx'] + $unduh) }}" class="tombol-kedua">Excel</a>
                </span>
            </div>
        @endforeach
    </div>
</section>
