@php
    $pintasan = [
        ['label' => 'Tambah guru', 'ikon' => 'heroicon-o-user-plus', 'url' => \App\Filament\Resources\PenggunaResource::getUrl('index')],
        ['label' => 'Impor murid', 'ikon' => 'heroicon-o-arrow-up-tray', 'url' => \App\Filament\Resources\MuridResource::getUrl('index')],
        ['label' => 'Tulis berita', 'ikon' => 'heroicon-o-newspaper', 'url' => \App\Filament\Resources\KontenResource::getUrl('index')],
        ['label' => 'Buat pengumuman', 'ikon' => 'heroicon-o-megaphone', 'url' => \App\Filament\Resources\PengumumanResource::getUrl('index')],
        ['label' => 'Unduh cadangan', 'ikon' => 'heroicon-o-circle-stack', 'url' => \App\Filament\Pages\CadanganData::getUrl()],
    ];
@endphp

<x-filament-widgets::widget>
    <div style="display:flex;flex-wrap:wrap;gap:.5rem">
        @foreach ($pintasan as $p)
            <x-filament::button :href="$p['url']" tag="a" color="gray" :icon="$p['ikon']">{{ $p['label'] }}</x-filament::button>
        @endforeach
    </div>
</x-filament-widgets::widget>
