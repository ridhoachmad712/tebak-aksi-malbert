<x-layouts.publik judul="Galeri">
    <section x-data="{ buka: null }" class="mx-auto max-w-6xl px-4 py-12">
        <h1 class="judul-halaman">Galeri Dokumentasi TEBAK AKSI</h1>
        <p class="teks-bantu mt-1">Foto kegiatan Teras Baca dan karya literasi murid yang dipilih admin sekolah.</p>
        <div class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-4">
            @forelse ($foto as $f)
                <button type="button" x-on:click="buka = @js(['url' => $f->url(), 'ket' => $f->keterangan ?? $f->sumber()])" class="group relative overflow-hidden rounded-lg">
                    <img src="{{ $f->url() }}" alt="{{ $f->keterangan ?? 'Dokumentasi TEBAK AKSI' }}" loading="lazy" class="aspect-square w-full object-cover">
                    <span class="absolute inset-x-0 bottom-0 bg-black/60 px-2 py-1 text-left text-xs font-bold text-white">{{ $f->keterangan ?? $f->sumber() }}</span>
                </button>
            @empty
                <p class="col-span-full text-slate-600">Belum ada foto di galeri.</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $foto->links() }}</div>

        <x-penampil-foto />
    </section>
</x-layouts.publik>
