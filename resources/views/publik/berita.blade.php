<x-layouts.publik judul="Berita">
    <section class="mx-auto max-w-3xl px-4 py-12">
        <h1 class="judul-halaman">Berita TEBAK AKSI</h1>
        <div class="mt-4 divide-y divide-garis border-y border-garis">
            @forelse ($berita as $b)
                @include('publik._kartu-berita', ['b' => $b])
            @empty
                <p class="py-4 text-slate-600">Belum ada berita yang diterbitkan.</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $berita->links() }}</div>
    </section>
</x-layouts.publik>
