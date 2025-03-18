<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static bool $isLazy = false;
    protected function getStats(): array
    {
        $countUsers = \App\Models\User::count(); 
        return [
            Stat::make('Users', $countUsers),
        ];
    }
}
