<?php

namespace App\Filament\Resources;

use App\Models;
use App\Filament\Resources\ClassroomResource\Pages;
use Filament\Forms;
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
        if (auth()->guard('web')->user()->role == 1) {
            return parent::getEloquentQuery()->where('teacher_id', auth()->guard('web')->user()->id)->groupBy('token');
        } else {
            // return parent::getEloquentQuery()->where('student_id', auth()->guard('web')->user()->id);
            return Models\Classroomable::query()
                ->where('classroomable_id', auth()->guard('web')->user()->id)
                ->with(['classroom'])
                ->groupBy('classroom_id')
                ->orderBy('created_at', 'desc');
        }
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {   
        if (auth()->guard('web')->user()->role == 1) {
            return $table
                ->columns([
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('name')
                            ->weight(FontWeight::Bold)
                            ->description(fn (Models\Classroom $record): string => $record->subject)
                            ->color('info'),
                    ])
                ])
                ->contentGrid([
                    'sm' => 1,
                    'md' => 2,
                    'xl' => 3,
                ])
                ->emptyStateHeading('No classrooms yet.')
                ->emptyStateDescription('You can create first.')
                ->paginated(false);
        }else{
            return $table
                ->columns([
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('classroom.name')
                            ->weight(FontWeight::Bold)
                            ->description(fn (Models\Classroomable $record): string => $record->classroom->subject)
                            ->color('info'),
                    ]),
                ])
                ->contentGrid([
                    'sm' => 1,
                    'md' => 2,
                    'xl' => 3,
                ])
                ->emptyStateHeading('No classrooms yet.')
                ->emptyStateDescription('You can join first or invite by teacher.')
                ->paginated(false);
        }

        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
            ])
            ->paginated(false);
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClassrooms::route('/'),
            'view' => Pages\ViewClassroom::route('/{record}'),
            'edit' => Pages\EditClassroom::route('/{record}/edit'),
        ];
    }
}
