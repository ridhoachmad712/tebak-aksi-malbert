@props(['tingkat' => null, 'ringkas' => false])

{{-- Skala perkembangan pembinaan sebagai lencana warna + teks (pengganti emoji, lihat DESIGN.md) --}}
@php
    $gaya = [
        'belum' => 'bg-rose-50 text-rose-800 ring-rose-200',
        'mulai' => 'bg-amber-50 text-amber-900 ring-amber-200',
        'positif' => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
        'konsisten' => 'bg-sky-50 text-sky-800 ring-sky-200',
    ];
    $info = \App\Models\KasusPembinaan::PERKEMBANGAN[$tingkat] ?? null;
@endphp

@if ($info)
    <span {{ $attributes->merge(['class' => 'lencana ring-1 ring-inset '.$gaya[$tingkat]]) }} title="{{ $info['label'] }}">
        {{ $ringkas ? $info['singkat'] : $info['label'] }}
    </span>
@else
    <span {{ $attributes->merge(['class' => 'lencana bg-slate-50 text-slate-500 ring-1 ring-inset ring-slate-200']) }}>{{ $ringkas ? '–' : 'Belum dipantau' }}</span>
@endif
