<div class="space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="judul-halaman">Karya Literasi</h1>
            <p class="teks-bantu">Catat karya murid, atau karya Anda sendiri, setiap kali ada karya baru.</p>
        </div>
        <a href="{{ route('guru.karya.baru') }}" wire:navigate class="tombol-utama"><x-heroicon-m-plus class="h-5 w-5" /> Tambah karya</a>
    </div>

    <div class="flex flex-wrap gap-2">
        @if ($daftarKelas->count() > 1)
            <select wire:model.live="kelas" class="isian w-auto pr-9 font-bold" aria-label="Saring kelas">
                <option value="">Semua kelas</option>
                @foreach ($daftarKelas as $k)
                    <option value="{{ $k->id }}">Kelas {{ $k->nama }}</option>
                @endforeach
            </select>
        @endif
        <input type="search" wire:model.live.debounce.400ms="cari" placeholder="Cari judul atau nama murid" aria-label="Cari karya" class="isian w-64">
    </div>

    @if ($karya->isNotEmpty())
        <ul class="kartu divide-y divide-garis">
            @foreach ($karya as $k)
                <li wire:key="karya-{{ $k->id }}">
                    <a href="{{ route('guru.karya.ubah', $k) }}" wire:navigate class="flex items-center gap-4 px-4 py-3 hover:bg-krem">
                        @if ($f = $k->foto->first())
                            <img src="{{ $f->url() }}" alt="" class="h-16 w-16 shrink-0 rounded-lg object-cover" loading="lazy">
                        @else
                            <span class="grid h-16 w-16 shrink-0 place-items-center rounded-lg border border-dashed border-slate-300 text-xs font-semibold text-slate-600">Tanpa foto</span>
                        @endif
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-extrabold">{{ $k->judul }}</span>
                            <span class="block truncate text-sm text-slate-700">{{ $k->namaPemilik() }}{{ $k->guru_pemilik_id ? ' (guru)' : '' }}</span>
                            <span class="block text-xs text-slate-600">{{ $k->namaJenis() }} · {{ $k->tanggal->format('d/m/Y') }}{{ $k->kelas ? ' · Kelas '.$k->kelas->nama : '' }}</span>
                        </span>
                        <x-heroicon-m-chevron-right class="h-5 w-5 shrink-0 text-slate-400" />
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <div class="kartu p-5 text-sm text-slate-600">
            @if ($cari)
                Tidak ada karya yang cocok dengan "{{ $cari }}".
            @else
                Belum ada karya literasi yang dicatat. Gunakan tombol Tambah karya saat murid atau Anda menghasilkan karya dari bacaan.
            @endif
        </div>
    @endif

    {{ $karya->links() }}
</div>
