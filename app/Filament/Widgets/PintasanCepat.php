<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class PintasanCepat extends Widget
{
    protected static ?int $sort = 4;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.pintasan-cepat';
}
