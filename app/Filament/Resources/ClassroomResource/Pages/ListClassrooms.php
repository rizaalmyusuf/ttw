<?php

namespace App\Filament\Resources\ClassroomResource\Pages;

use App\Models;
use App\Filament\Resources\ClassroomResource;
use Illuminate\Support\Str;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
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
                    ->form([
                        TextInput::make('name')
                            ->label('Classroom Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('X TKI 1')
                            ->autofocus(),
                        TextInput::make('subject')
                            ->label('Subject')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Informatics'),
                    ])
                    ->action(function (array $data) {
                        Models\Classroom::create([
                            'token' => Str::random(7),
                            'name' => $data['name'],
                            'subject' => $data['subject'],
                            'teacher_id' => auth()->guard('web')->user()->id,
                        ]);

                        Notification::make()
                            ->title('Classroom created!')
                            ->success()
                            ->send();

                        return redirect()->to('/classrooms'.'/'.Models\Classroom::latest('id')->first()->id);
                    })
                    ->createAnother(false)
                    ->modalIcon('heroicon-s-squares-plus')
                    ->modalHeading('Create Classroom')
                    ->modalDescription('Create a classroom to make students join the classroom!')
                    ->modalSubmitActionLabel('Create')
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
                        
                        $classroom = Models\Classroom::where('token', $data['token'])->first();

                        if ($classroom === null) {
                            Notification::make()
                                ->title('Classroom not found!')
                                ->danger()
                                ->send();
                            return;
                        }

                        if (Models\Classroomable::where('classroom_id', $classroom->id)
                            ->where('classroomable_id', auth()->guard('web')->user()->id)
                            ->where('classroomable_type', 'App\Models\User')
                            ->exists()) {
                            Notification::make()
                                ->title('You are already a member of this classroom!')
                                ->danger()
                                ->send();
                            return;
                        }else{
                            Models\Classroomable::create([
                                'classroom_id' => $classroom->id,
                                'classroomable_id' => auth()->guard('web')->user()->id,
                                'classroomable_type' => 'App\Models\User',
                            ]);

                            Notification::make()
                                ->title('Classroom joined successfully!')
                                ->success()
                                ->send();
                            return;
                        }
                    })
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-s-squares-2x2')
                    ->modalHeading('Join Classroom')
                    ->modalDescription('Enter the classroom token to join!')
                    ->modalSubmitActionLabel('Join')
                    ->modalWidth('lg')
            ];
        }

        return [];
    }
}
