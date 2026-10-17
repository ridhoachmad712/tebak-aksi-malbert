@props(['sasaran'])

@php $daftar = \App\Models\Pengumuman::berlaku($sasaran)->limit(3)->get(); @endphp

@if ($daftar->isNotEmpty())
    <div class="border-b border-amber-200 bg-amber-50" role="region" aria-label="Pengumuman">
        @foreach ($daftar as $p)
            <div class="mx-auto flex max-w-6xl items-start gap-2 px-4 py-2.5 text-sm text-amber-950">
                <x-heroicon-o-megaphone class="mt-0.5 h-5 w-5 shrink-0" />
                <p>
                    <span class="font-semibold">{{ $p->isi }}</span>
                    @if ($p->tautan)
                        <a href="{{ $p->tautan }}" class="ml-1 font-bold underline underline-offset-2">Selengkapnya</a>
                    @endif
                </p>
            </div>
        @endforeach
    </div>
@endif
