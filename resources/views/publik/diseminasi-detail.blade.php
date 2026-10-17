<x-layouts.publik :judul="$d->judul">
    <section class="mx-auto max-w-5xl px-4 py-12">
        <a href="{{ route('diseminasi') }}" class="text-sm font-bold text-slate-500 hover:text-slate-700">← Diseminasi</a>
        <h1 class="judul-halaman mt-1">{{ $d->judul }}</h1>
        <p class="mt-1 font-bold text-toska-700">{{ $d->tanggal->translatedFormat('d F Y') }}{{ $d->tempat ? ' · '.$d->tempat : '' }}</p>
        @if ($d->keterangan)
            <p class="mt-4 whitespace-pre-line text-slate-700">{{ $d->keterangan }}</p>
        @endif
        <div class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-3">
            @foreach ($d->foto as $f)
                <a href="{{ $f->url() }}" target="_blank"><img src="{{ $f->url() }}" alt="{{ $f->keterangan ?? $d->judul }}" loading="lazy" class="aspect-[4/3] w-full rounded-2xl object-cover"></a>
            @endforeach
        </div>
    </section>
</x-layouts.publik>
