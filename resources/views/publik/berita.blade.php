<x-layouts.publik judul="Berita">
    <section class="mx-auto max-w-6xl px-4 py-12">
        <h1 class="judul-halaman">Berita & Kabar TEBAK AKSI</h1>
        <div class="mt-6 grid gap-4 md:grid-cols-3">
            @forelse ($berita as $b)
                @include('publik._kartu-berita', ['b' => $b])
            @empty
                <p class="text-slate-500">Belum ada berita.</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $berita->links() }}</div>
    </section>
</x-layouts.publik>
