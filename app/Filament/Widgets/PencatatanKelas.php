<?php

namespace App\Filament\Widgets;

use App\Services\TugasAdmin;
use Filament\Widgets\Widget;

class PencatatanKelas extends Widget
{
    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.pencatatan-kelas';

    protected function getViewData(): array
    {
        return ['kelas' => TugasAdmin::pencatatanKelas()];
    }
}
