<?php

namespace App\Filament\Widgets;

use App\Services\TugasAdmin;
use Filament\Widgets\Widget;

class PerluDitindaklanjuti extends Widget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.perlu-ditindaklanjuti';

    protected function getViewData(): array
    {
        return ['tugas' => TugasAdmin::tindakLanjut()];
    }
}
