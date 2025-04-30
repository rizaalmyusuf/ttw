<?php

namespace App\Filament\Resources\ClassroomResource\Pages;

use App\Filament\Resources\ClassroomResource;
use App\Models\Classroom;
use Filament\Actions\CreateAction;
use Illuminate\Support\Str;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;

class ListClassrooms extends ListRecords
{
    protected static string $resource = ClassroomResource::class;

    protected function getHeaderActions(): array
    {
        if (auth()->guard('web')->user()->role === 1) {
            return [
                CreateAction::make()
                    ->icon('heroicon-s-squares-plus')
                    ->label('Create Classroom')
                    ->color('primary')
                    ->fillForm(fn (array $data): array => [
                        'token' => Str::random(5),
                        'teacher_id' => auth()->guard('web')->user()->name,
                    ])
                    ->form([
                        TextInput::make('token')
                            ->label('Classroom Token')
                            ->readOnly()
                            ->required()
                            ->unique()
                            ->maxLength(255)
                            ->placeholder('Classroom Token'),
                        TextInput::make('name')
                            ->label('Classroom Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Classroom Name')
                            ->autofocus(),
                        TextInput::make('subject')
                            ->label('Subject')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Subject'),
                        TextInput::make('teacher_id')
                            ->label('Teacher Name')
                            ->readOnly()
                            ->required()
                    ])
                    ->modalIcon('heroicon-s-squares-plus')
                    ->modalHeading('Create Classroom')
                    ->modalSubheading('Create the classroom to make students join the classroom')
                    ->modalButton('Create')
                    ->modalWidth('lg')                    
            ];
        }elseif (auth()->guard('web')->user()->role === 2) {
            return [
                CreateAction::make()
                    ->icon('heroicon-s-squares-2x2')
                    ->label('Join Classroom')
                    ->color('primary')
                    ->form([
                        TextInput::make('token')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Classroom Token'),
                    ])
                    ->createAnother(false)
                    ->action(function (array $data) {
                        // Logic to join the classroom using the token
                        // For example, you can use Classroom::where('token', $data['token'])->first();
                        // and then attach the user to the classroom.
                    })
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-s-squares-2x2')
                    ->modalHeading('Join Classroom')
                    ->modalSubheading('Enter the classroom token to join')
                    ->modalButton('Join')
                    ->modalWidth('lg')
            ];
        }

        return [
            // Actions\CreateAction::make()
            //     ->icon('heroicon-s-squares-plus')
            //     ->label('Create Classroom')
            //     ->color('primary')
        ];
    }
}
