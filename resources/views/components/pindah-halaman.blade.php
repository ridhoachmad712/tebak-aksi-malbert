{{-- Bilah pindah halaman khusus admin: Halaman Admin, Monev, atau Guru --}}
@php $aktif = \App\Support\HalamanAdmin::aktif(); @endphp

<nav aria-label="Pindah halaman" class="border-b border-garis bg-slate-50">
    <div class="mx-auto flex max-w-6xl items-center gap-2 overflow-x-auto px-4 py-1.5 text-sm">
        <span class="shrink-0 font-semibold text-slate-600">Pindah ke:</span>
        @foreach (\App\Support\HalamanAdmin::daftar() as $h)
            <a href="{{ $h['url'] }}" @if ($h['kunci'] !== 'admin') wire:navigate @endif
               @if ($aktif === $h['kunci']) aria-current="page" @endif
               @class(['inline-flex min-h-9 shrink-0 items-center gap-1.5 rounded-md px-2.5 font-bold',
                   'bg-slate-800 text-white' => $aktif === $h['kunci'],
                   'text-slate-700 hover:bg-white' => $aktif !== $h['kunci']])>
                <x-dynamic-component :component="$h['ikon']" class="h-4 w-4" />
                {{ $h['label'] }}
            </a>
        @endforeach
    </div>
</nav>
