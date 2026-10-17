<a href="{{ route('halaman', $b->slug) }}" class="group flex gap-4 py-4">
    @if ($b->urlGambar())
        <img src="{{ $b->urlGambar() }}" alt="" class="hidden h-24 w-36 shrink-0 rounded-lg object-cover sm:block" loading="lazy">
    @endif
    <div class="min-w-0">
        <p class="text-sm font-semibold text-slate-600">{{ $b->terbit_pada?->translatedFormat('d F Y') }}</p>
        <h3 class="text-lg font-extrabold group-hover:text-brand-800 group-hover:underline">{{ $b->judul }}</h3>
        @if ($b->ringkasan)
            <p class="mt-0.5 line-clamp-2 text-slate-700">{{ $b->ringkasan }}</p>
        @endif
    </div>
</a>
