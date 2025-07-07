<?php

namespace App\Filament\Resources;

use App\Models;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Builder;

class ClassroomResource extends Resource
{
    protected static ?string $model = Models\Classroom::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $activeNavigationIcon = 'heroicon-s-academic-cap';

    public static function getEloquentQuery(): Builder
    {
        if ((new static)->role() === 1) {
            return parent::getEloquentQuery()->where('teacher_id', auth()->guard('web')->user()->id)->groupBy('token');
        } else {
            return Models\Classroomable::query()
                ->where('classroomable_id', auth()->guard('web')->user()->id)
                ->with(['classroom'])
                ->groupBy('classroom_id')
                ->orderBy('created_at', 'asc');
        }
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
                ->columns([
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make(((new static)->role() === 2 ? 'classroom.' : '').'name')
                            ->weight(FontWeight::Bold)
                            ->description(function (Models\Classroom $recordForTeacher, Models\Classroomable $recordForStudent){
                                return (new static)->role() === 1 ? $recordForTeacher->subject : $recordForStudent->classroom->subject;
                            })
                            ->color('info'),
                    ])
                ])
                ->contentGrid([
                    'sm' => 1,
                    'md' => 2,
                    'xl' => 3,
                ])
                ->emptyStateHeading('No classrooms yet.')
                ->emptyStateDescription((new static)->role() === 1 ? 'You can create first.' : 'You can join first or invite by teacher.')
                ->paginated(false);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ClassroomResource\Pages\ListClassrooms::route('/'),
            'view' => ClassroomResource\Pages\ViewClassroom::route('/{record}'),
        ];
    }

    protected function role(): int {
        return auth()->guard()->user()->role;
    }
}
