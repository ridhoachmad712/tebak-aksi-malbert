@props(['nilai', 'target', 'teks' => null])

{{-- Hanya nilai di bawah target yang diberi warna; selain itu netral --}}
@php
    $warna = \App\Services\RingkasanMonev::warna($nilai, (float) $target);
    $kelas = match ($warna) {
        'amber' => 'bg-amber-50 text-amber-900 ring-1 ring-inset ring-amber-200',
        'rose' => 'bg-rose-50 text-rose-800 ring-1 ring-inset ring-rose-200',
        default => 'text-slate-900',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-block rounded-md px-1.5 py-0.5 font-bold tabular-nums $kelas"]) }} @if ($warna) title="Di bawah target {{ \App\Services\Rekap::persenTeks((float) $target) }}" @endif>{{ $teks ?? \App\Services\Rekap::persenTeks($nilai) }}</span>
