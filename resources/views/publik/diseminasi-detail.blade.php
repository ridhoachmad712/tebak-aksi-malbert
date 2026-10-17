<x-layouts.publik :judul="$d->judul">
    <section class="mx-auto max-w-5xl px-4 py-12">
        <a href="{{ route('diseminasi') }}" class="inline-flex min-h-11 items-center gap-1 text-sm font-bold text-slate-600 hover:text-slate-900"><x-heroicon-m-chevron-left class="h-4 w-4" /> Diseminasi</a>
        <h1 class="judul-halaman mt-1">{{ $d->judul }}</h1>
        <p class="mt-1 font-semibold text-slate-600">{{ $d->tanggal->translatedFormat('d F Y') }}{{ $d->tempat ? ' · '.$d->tempat : '' }}</p>
        @if ($d->keterangan)
            <p class="mt-4 whitespace-pre-line text-slate-700">{{ $d->keterangan }}</p>
        @endif
        <div class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-3">
            @foreach ($d->foto as $f)
                <a href="{{ $f->url() }}" target="_blank"><img src="{{ $f->url() }}" alt="{{ $f->keterangan ?? $d->judul }}" loading="lazy" class="aspect-[4/3] w-full rounded-lg object-cover"></a>
            @endforeach
        </div>
    </section>
</x-layouts.publik>
