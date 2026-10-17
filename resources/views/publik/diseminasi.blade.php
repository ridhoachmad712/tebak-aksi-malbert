<x-layouts.publik judul="Diseminasi">
    <section class="mx-auto max-w-6xl px-4 py-12">
        <h1 class="judul-halaman">Diseminasi TEBAK AKSI</h1>
        <p class="teks-bantu mt-1">Dokumentasi kegiatan berbagi praktik baik program TEBAK AKSI dengan sekolah lain.</p>
        <div class="mt-6 grid gap-4 md:grid-cols-3">
            @forelse ($daftar as $d)
                <a href="{{ route('diseminasi.detail', $d) }}" class="kartu group overflow-hidden transition hover:border-brand-300">
                    <div class="aspect-[16/9] bg-slate-100">
                        @if ($f = $d->foto->first())
                            <img src="{{ $f->url() }}" alt="{{ $d->judul }}" class="h-full w-full object-cover" loading="lazy">
                        @else
                            <div class="grid h-full place-items-center text-sm font-semibold text-slate-600">Belum ada foto</div>
                        @endif
                    </div>
                    <div class="p-5">
                        <p class="text-sm font-semibold text-slate-600">{{ $d->tanggal->translatedFormat('d F Y') }}{{ $d->tempat ? ' · '.$d->tempat : '' }}</p>
                        <h2 class="mt-1 text-lg font-extrabold group-hover:text-brand-800">{{ $d->judul }}</h2>
                        <p class="text-sm text-slate-600">{{ $d->foto->count() }} foto</p>
                    </div>
                </a>
            @empty
                <p class="text-slate-600">Belum ada dokumentasi diseminasi.</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $daftar->links() }}</div>
    </section>
</x-layouts.publik>
