<?php

namespace App\Filament\Widgets;

use App\Services\TugasAdmin;
use Filament\Widgets\Widget;

class PersiapanSistem extends Widget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.persiapan-sistem';

    public static function canView(): bool
    {
        return ! TugasAdmin::persiapanSelesai();
    }

    protected function getViewData(): array
    {
        return ['langkah' => TugasAdmin::persiapan()];
    }
}
