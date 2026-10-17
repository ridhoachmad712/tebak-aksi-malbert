@props(['persen', 'teks' => null])

@php
    $k = \App\Services\Rekap::kategori($persen);
    $warna = [
        'emerald' => 'bg-emerald-100 text-emerald-800',
        'sky' => 'bg-sky-100 text-sky-800',
        'amber' => 'bg-amber-100 text-amber-800',
        'rose' => 'bg-rose-100 text-rose-800',
        'gray' => 'bg-slate-100 text-slate-500',
    ][$k['warna']];
@endphp

<span {{ $attributes->merge(['class' => "lencana $warna"]) }} title="{{ $k['label'] }}">{{ $teks ?? ($persen === null ? '-' : $persen.'%') }}</span>
