<?php

namespace App\Filament\Widgets;

use App\Models;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static bool $isLazy = false;
    protected function getStats(): array
    {
        if(auth()->guard()->user()->role === 0){
            return [
                Stat::make('Users', Models\User::count())
                    ->icon('heroicon-s-users'),
            ];
        }elseif(auth()->guard()->user()->role === 1){
            return [
                Stat::make('Your Classrooms',Models\Classroom::where('teacher_id', auth()->guard()->user()->id)->count())
                    ->icon('heroicon-s-academic-cap'),
            ];
        }else{
            return [
                Stat::make('Your Joined Classrooms',Models\Classroomable::where('classroomable_id', auth()->guard()->user()->id)->count())
                    ->icon('heroicon-s-academic-cap')
            ];
        }
        return [];
    }
}
