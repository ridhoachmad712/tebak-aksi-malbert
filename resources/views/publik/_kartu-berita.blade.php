<a href="{{ route('halaman', $b->slug) }}" class="kartu group overflow-hidden transition hover:border-brand-300">
    <div class="aspect-[16/9] bg-gradient-to-br from-orange-100 to-teal-100">
        @if ($b->urlGambar())
            <img src="{{ $b->urlGambar() }}" alt="{{ $b->judul }}" class="h-full w-full object-cover" loading="lazy">
        @else
            <div class="grid h-full place-items-center text-5xl">📰</div>
        @endif
    </div>
    <div class="p-5">
        <p class="text-xs font-bold text-brand-600">{{ $b->terbit_pada?->translatedFormat('d F Y') }}</p>
        <h3 class="mt-1 text-lg font-extrabold group-hover:text-brand-700">{{ $b->judul }}</h3>
        @if ($b->ringkasan)
            <p class="mt-1 line-clamp-3 text-sm text-slate-600">{{ $b->ringkasan }}</p>
        @endif
    </div>
</a>
