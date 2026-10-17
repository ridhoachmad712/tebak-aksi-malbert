<?php

namespace App\Filament\Widgets;

use App\Services\TugasAdmin;
use Filament\Widgets\Widget;

class AngkaRingkas extends Widget
{
    protected static ?int $sort = 5;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.angka-ringkas';

    protected function getViewData(): array
    {
        return ['angka' => TugasAdmin::angka()];
    }
}
