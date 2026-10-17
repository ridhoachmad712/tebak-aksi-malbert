<x-layouts.publik :judul="$konten->judul">
    <article class="mx-auto max-w-3xl px-4 py-12">
        <p class="text-sm font-semibold text-slate-600">{{ $konten->jenis === 'berita' ? 'Berita · '.$konten->terbit_pada?->translatedFormat('d F Y') : 'TEBAK AKSI' }}</p>
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $konten->judul }}</h1>
        @if ($konten->ringkasan)
            <p class="mt-3 text-lg text-slate-600">{{ $konten->ringkasan }}</p>
        @endif
        @if ($konten->urlGambar())
            <img src="{{ $konten->urlGambar() }}" alt="{{ $konten->judul }}" class="mt-6 w-full rounded-xl">
        @endif
        <div class="isi-konten mt-6 text-slate-700">
            {!! strip_tags((string) $konten->isi, '<p><br><b><strong><i><em><u><s><a><ul><ol><li><h2><h3><h4><blockquote><img><figure><figcaption><hr><table><thead><tbody><tr><th><td>') !!}
        </div>
    </article>
</x-layouts.publik>
