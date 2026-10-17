<x-layouts.publik judul="Galeri">
    <section x-data="{ buka: null }" class="mx-auto max-w-6xl px-4 py-12">
        <h1 class="judul-halaman">Galeri Dokumentasi TEBAK AKSI</h1>
        <p class="mt-1 text-slate-600">Potret kegiatan Teras Baca dan karya literasi murid.</p>
        <div class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-4">
            @forelse ($foto as $f)
                <button type="button" x-on:click="buka = @js(['url' => $f->url(), 'ket' => $f->keterangan ?? $f->sumber()])" class="group relative overflow-hidden rounded-2xl">
                    <img src="{{ $f->url() }}" alt="{{ $f->keterangan ?? 'Dokumentasi TEBAK AKSI' }}" loading="lazy" class="aspect-square w-full object-cover transition group-hover:scale-105">
                    <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 p-2 text-left text-xs font-bold text-white">{{ $f->keterangan ?? $f->sumber() }}</span>
                </button>
            @empty
                <p class="col-span-full text-slate-500">Belum ada foto di galeri.</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $foto->links() }}</div>

        <div x-cloak x-show="buka" x-on:click="buka = null" x-on:keydown.escape.window="buka = null" class="fixed inset-0 z-50 grid place-items-center bg-black/80 p-4">
            <figure class="max-w-4xl" x-on:click.stop>
                <img :src="buka?.url" alt="" class="max-h-[80vh] rounded-2xl">
                <figcaption class="mt-2 text-center font-bold text-white" x-text="buka?.ket"></figcaption>
            </figure>
        </div>
    </section>
</x-layouts.publik>
