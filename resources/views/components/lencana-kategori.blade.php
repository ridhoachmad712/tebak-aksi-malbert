@props(['persen', 'teks' => null])

@php
    $k = \App\Services\Rekap::kategori($persen);
    $warna = [
        'emerald' => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
        'sky' => 'bg-sky-50 text-sky-800 ring-sky-200',
        'amber' => 'bg-amber-50 text-amber-900 ring-amber-200',
        'rose' => 'bg-rose-50 text-rose-800 ring-rose-200',
        'gray' => 'bg-slate-50 text-slate-600 ring-slate-200',
    ][$k['warna']];
@endphp

<span {{ $attributes->merge(['class' => "lencana ring-1 ring-inset tabular-nums $warna"]) }} title="{{ $k['label'] }}">{{ $teks ?? ($persen === null ? '–' : $persen.'%') }}</span>
