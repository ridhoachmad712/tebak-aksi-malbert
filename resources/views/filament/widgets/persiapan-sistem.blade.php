@php
    $selesai = collect($langkah)->where('selesai', true)->count();
    $total = count($langkah);
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Persiapan sistem</x-slot>
        <x-slot name="description">{{ $selesai }} dari {{ $total }} langkah selesai. Bagian ini hilang sendiri setelah semua langkah beres.</x-slot>

        <div style="height:.5rem;border-radius:999px;background:rgb(243 244 246);overflow:hidden;margin-bottom:1rem" role="progressbar" aria-valuenow="{{ $selesai }}" aria-valuemin="0" aria-valuemax="{{ $total }}">
            <div style="height:100%;width:{{ round($selesai / $total * 100) }}%;background:var(--primary-600)"></div>
        </div>

        <ul style="display:grid;grid-template-columns:repeat(auto-fill,minmax(20rem,1fr));gap:.25rem 1.5rem;margin:0;padding:0;list-style:none">
            @foreach ($langkah as $l)
                <li>
                    <a href="{{ $l['url'] }}" style="display:flex;align-items:center;gap:.6rem;min-height:2.5rem;padding:.25rem .4rem;border-radius:.5rem;text-decoration:none;color:{{ $l['selesai'] ? 'rgb(107 114 128)' : 'rgb(17 24 39)' }}"
                       onmouseover="this.style.background='rgb(249 250 251)'" onmouseout="this.style.background='transparent'">
                        @if ($l['selesai'])
                            <x-filament::icon icon="heroicon-s-check-circle" style="height:1.35rem;width:1.35rem;flex-shrink:0;color:rgb(22 163 74)" />
                            <span style="text-decoration:line-through">{{ $l['label'] }}</span>
                        @else
                            <span style="height:1.35rem;width:1.35rem;flex-shrink:0;border:2px solid rgb(209 213 219);border-radius:999px"></span>
                            <span style="font-weight:600">{{ $l['label'] }}</span>
                            <x-filament::icon icon="heroicon-m-chevron-right" style="height:1rem;width:1rem;margin-left:auto;color:rgb(156 163 175)" />
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </x-filament::section>
</x-filament-widgets::widget>
