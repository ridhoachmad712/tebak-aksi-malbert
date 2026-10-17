<x-layouts.publik judul="Diseminasi">
    <section class="mx-auto max-w-6xl px-4 py-12">
        <h1 class="judul-halaman">Diseminasi TEBAK AKSI</h1>
        <p class="mt-1 text-slate-600">Dokumentasi kegiatan berbagi praktik baik program TEBAK AKSI.</p>
        <div class="mt-6 grid gap-4 md:grid-cols-3">
            @forelse ($daftar as $d)
                <a href="{{ route('diseminasi.detail', $d) }}" class="kartu group overflow-hidden transition hover:border-brand-300">
                    <div class="aspect-[16/9] bg-gradient-to-br from-teal-100 to-orange-100">
                        @if ($f = $d->foto->first())
                            <img src="{{ $f->url() }}" alt="{{ $d->judul }}" class="h-full w-full object-cover" loading="lazy">
                        @else
                            <div class="grid h-full place-items-center text-5xl">🎤</div>
                        @endif
                    </div>
                    <div class="p-5">
                        <p class="text-xs font-bold text-toska-700">{{ $d->tanggal->translatedFormat('d F Y') }}{{ $d->tempat ? ' · '.$d->tempat : '' }}</p>
                        <h2 class="mt-1 text-lg font-extrabold group-hover:text-brand-700">{{ $d->judul }}</h2>
                        <p class="text-sm text-slate-500">{{ $d->foto->count() }} foto</p>
                    </div>
                </a>
            @empty
                <p class="text-slate-500">Belum ada dokumentasi diseminasi.</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $daftar->links() }}</div>
    </section>
</x-layouts.publik>
