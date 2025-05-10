<?php

namespace App\Filament\Resources;

use App\Models;
use App\Filament\Resources\ClassroomResource\Pages;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClassroomResource extends Resource
{
    protected static ?string $model = Models\Classroom::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $activeNavigationIcon = 'heroicon-s-academic-cap';

    protected static string $view = 'filament.pages.card-view';

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
                    Tables\Columns\TextColumn::make('name')
                        ->label('Classrooms')
                        ->description(fn (Models\Classroom $record): string => $record->subject)
                        ->color('primary'),
                ])
                ->paginated(false);
        }else{
            return $table
                ->columns([
                    Tables\Columns\TextColumn::make('classroom.name')
                        ->label('Classrooms')
                        ->description(fn (Models\Classroomable $record): string => $record->classroom->subject)
                        ->color('primary'),
                ])
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
            // 'create' => Pages\CreateClassroom::route('/create'),
            'view' => Pages\ViewClassroom::route('/{record}'),
            'edit' => Pages\EditClassroom::route('/{record}/edit'),
        ];
    }
}
