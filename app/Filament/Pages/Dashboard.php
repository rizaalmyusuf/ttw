<?php
namespace App\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Widgets;

class Dashboard extends \Filament\Pages\Dashboard {
    protected static string $routePath = '/';
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $activeNavigationIcon = 'heroicon-s-home';
    protected static string $view = 'filament.pages.dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            Widgets\AccountWidget::class,
            Widgets\FilamentInfoWidget::class,
            \App\Filament\Widgets\StatsOverview::class,
        ];
    }
}
