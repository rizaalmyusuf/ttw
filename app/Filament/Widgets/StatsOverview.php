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
            $classrooms = Models\Classroom::where('teacher_id', auth()->guard()->user()->id)->get();
            $students = Models\Classroomable::whereIn('classroom_id', $classrooms->pluck('id'))->groupBy('classroomable_id')->get();
            return [
                Stat::make('Your Classrooms',$classrooms->count())
                    ->icon('heroicon-s-academic-cap'),
                Stat::make('Your Students', $students->count())
                    ->icon('heroicon-s-users'),
            ];
        }else{
            return [
                Stat::make('Your Joined Classrooms',Models\Classroomable::where('classroomable_id', auth()->guard()->user()->id)->groupBy('classroom_id')->get()->count())
                    ->icon('heroicon-s-academic-cap')
            ];
        }
        return [];
    }
}
