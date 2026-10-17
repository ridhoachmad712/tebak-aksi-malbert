@props(['kecil' => false])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <svg viewBox="0 0 48 48" class="{{ $kecil ? 'h-8 w-8' : 'h-10 w-10' }} shrink-0" aria-hidden="true">
        <rect width="48" height="48" rx="14" fill="#f97316" />
        <path d="M9 15.5c4.6-1.6 9.6-1.2 15 1.8v19.2c-5.4-3-10.4-3.4-15-1.8z" fill="#fff" />
        <path d="M39 15.5c-4.6-1.6-9.6-1.2-15 1.8v19.2c5.4-3 10.4-3.4 15-1.8z" fill="#ffedd5" />
        <circle cx="35" cy="11" r="4" fill="#fde047" />
        <path d="M13 21.5c2.6-.6 5.2-.3 8 1M13 26c2.6-.6 5.2-.3 8 1" stroke="#fb923c" stroke-width="1.8" stroke-linecap="round" />
    </svg>
    <span class="leading-tight">
        <span class="block font-extrabold tracking-tight text-slate-800 {{ $kecil ? 'text-base' : 'text-lg' }}">TEBAK <span class="text-brand-500">AKSI</span></span>
        @unless ($kecil)
            <span class="block text-[11px] font-semibold text-slate-500">Teras Baca Kelas Asah Karakter &amp; Literasi</span>
        @endunless
    </span>
</span>
