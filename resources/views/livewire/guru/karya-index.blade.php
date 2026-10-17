<div class="space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="judul-halaman">Karya Literasi</h1>
            <p class="text-sm text-slate-500">Catat karya murid atau karya Anda sendiri setiap kali ada karya baru.</p>
        </div>
        <a href="{{ route('guru.karya.baru') }}" wire:navigate class="tombol-utama"><x-heroicon-m-plus class="h-5 w-5" /> Tambah Karya</a>
    </div>

    <div class="flex flex-wrap gap-2">
        @if ($daftarKelas->count() > 1)
            <select wire:model.live="kelas" class="isian w-auto py-2 pr-9 font-bold">
                <option value="">Semua kelas</option>
                @foreach ($daftarKelas as $k)
                    <option value="{{ $k->id }}">Kelas {{ $k->nama }}</option>
                @endforeach
            </select>
        @endif
        <input type="search" wire:model.live.debounce.400ms="cari" placeholder="Cari judul atau nama murid…" class="isian w-64 py-2">
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($karya as $k)
            <a href="{{ route('guru.karya.ubah', $k) }}" wire:navigate wire:key="karya-{{ $k->id }}" class="kartu group overflow-hidden transition hover:border-brand-300">
                <div class="aspect-[4/3] bg-gradient-to-br from-violet-100 via-orange-50 to-teal-100">
                    @if ($f = $k->foto->first())
                        <img src="{{ $f->url() }}" alt="{{ $k->judul }}" class="h-full w-full object-cover" loading="lazy">
                    @else
                        <div class="grid h-full place-items-center text-5xl">🎨</div>
                    @endif
                </div>
                <div class="p-4">
                    <span class="lencana bg-violet-100 text-violet-700">{{ $k->namaJenis() }}</span>
                    @if ($k->guru_pemilik_id)
                        <span class="lencana bg-toska-100 text-toska-800">Karya guru</span>
                    @endif
                    <p class="mt-2 font-extrabold group-hover:text-brand-700">{{ $k->judul }}</p>
                    <p class="truncate text-sm text-slate-600">{{ $k->namaPemilik() }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $k->tanggal->format('d/m/Y') }}{{ $k->kelas ? ' · Kelas '.$k->kelas->nama : '' }}</p>
                </div>
            </a>
        @empty
            <div class="kartu p-8 text-center sm:col-span-2 lg:col-span-3">
                <div class="text-5xl">🎨</div>
                <p class="mt-2 font-bold">Belum ada karya literasi.</p>
                <a href="{{ route('guru.karya.baru') }}" wire:navigate class="tombol-utama mt-4">Tambah karya pertama</a>
            </div>
        @endforelse
    </div>

    {{ $karya->links() }}
</div>
