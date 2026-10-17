@props(['kecil' => false])

@php $logo = \App\Models\Pengaturan::ambil('logo_sekolah'); @endphp

{{-- Motif identitas: huruf pembentuk akronim diberi warna aksen (lihat DESIGN.md) --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    @if ($logo)
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logo) }}" alt="Logo {{ \App\Models\Pengaturan::ambil('nama_sekolah') }}" class="{{ $kecil ? 'h-9 w-9' : 'h-10 w-10' }} shrink-0 object-contain">
    @else
        <svg viewBox="0 0 40 40" class="{{ $kecil ? 'h-8 w-8' : 'h-9 w-9' }} shrink-0" aria-hidden="true">
            <rect width="40" height="40" rx="9" fill="#c2410c" />
            <path d="M8 12.5c3.8-1.3 8-1 12 1.4v16c-4-2.4-8.2-2.7-12-1.4z" fill="#fff" />
            <path d="M32 12.5c-3.8-1.3-8-1-12 1.4v16c4-2.4 8.2-2.7 12-1.4z" fill="#ffedd5" />
        </svg>
    @endif
    <span class="leading-tight">
        <span class="block font-extrabold tracking-tight text-slate-800 {{ $kecil ? 'text-base' : 'text-lg' }}">TEBAK AKSI</span>
        @unless ($kecil)
            <span class="block text-xs font-semibold text-slate-600"><b class="text-brand-700">TE</b>ras <b class="text-brand-700">BA</b>ca <b class="text-brand-700">K</b>elas <b class="text-brand-700">A</b>sah Karakter dan litera<b class="text-brand-700">SI</b></span>
        @endunless
    </span>
</span>
