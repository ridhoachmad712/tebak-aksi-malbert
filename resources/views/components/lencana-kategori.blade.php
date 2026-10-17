@props(['persen', 'teks' => null])

{{-- Hanya kategori rendah (Cukup, Perlu Bimbingan) yang diberi warna agar mata langsung tertuju ke sana --}}
@php
    $k = \App\Services\Rekap::kategori($persen);
    $warna = [
        'amber' => 'bg-amber-50 text-amber-900 ring-amber-200',
        'rose' => 'bg-rose-50 text-rose-800 ring-rose-200',
        'gray' => 'bg-slate-50 text-slate-600 ring-slate-200',
    ][$k['warna']] ?? 'bg-white text-slate-800 ring-slate-200';
@endphp

<span {{ $attributes->merge(['class' => "lencana ring-1 ring-inset tabular-nums $warna"]) }} title="{{ $k['label'] }}">{{ $teks ?? \App\Services\Rekap::persenTeks($persen) }}</span>
