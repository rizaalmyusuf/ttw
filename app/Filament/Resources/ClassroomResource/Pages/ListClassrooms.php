<?php

namespace App\Filament\Resources\ClassroomResource\Pages;

use App\Models;
use App\Filament\Resources\ClassroomResource;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;

class ListClassrooms extends ListRecords
{
    protected static string $resource = ClassroomResource::class;

    protected function getHeaderActions(): array
    {
        $role = auth()->guard()->user()->role;

        return [
            CreateAction::make()
                    ->icon($role === 1 ? 'heroicon-s-squares-plus' : 'heroicon-s-squares-2x2')
                    ->label($role === 1 ? 'Create Classroom' : 'Join Classroom')
                    ->color('primary')
                    ->form([
                        TextInput::make('name')
                            ->visible(fn () => $role === 1)
                            ->label('Classroom Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('X TKI 1')
                            ->autofocus(),
                        TextInput::make('subject')
                            ->visible(fn () => $role === 1)
                            ->label('Subject')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Informatics'),
                        TextInput::make('token')
                            ->visible(fn () => $role === 2)
                            ->required()
                            ->maxLength(255)
                            ->placeholder(Str::random(7)),
                    ])
                    ->action(function (array $data) {
                        if(auth()->guard()->user()->role === 1){
                            Models\Classroom::create([
                                'token' => Str::random(7),
                                'name' => $data['name'],
                                'subject' => $data['subject'],
                                'teacher_id' => auth()->guard('web')->user()->id,
                            ]);
    
                            Notification::make()
                                ->title('Congrats!')
                                ->body('Classroom created successfully!')                            
                                ->success()
                                ->send();
    
                            return redirect()->to('/classrooms'.'/'.Models\Classroom::latest('id')->first()->id);
                        }else{
                            $classroom = Models\Classroom::where('token', $data['token'])->first();

                            if ($classroom === null) {
                                Notification::make()
                                    ->title('Failed!')
                                    ->body('Ouh no, classroom not found!')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            if (Models\Classroomable::where('classroom_id', $classroom->id)
                                ->where('classroomable_id', auth()->guard('web')->user()->id)
                                ->where('classroomable_type', 'App\Models\User')
                                ->exists()) {
                                Notification::make()
                                    ->title('Failed!')
                                    ->body('Don\'t worry, you are already a member of this classroom!')
                                    ->warning()
                                    ->send();
                                return;
                            }else{
                                Models\Classroomable::create([
                                    'classroom_id' => $classroom->id,
                                    'classroomable_id' => auth()->guard('web')->user()->id,
                                    'classroomable_type' => 'App\Models\User',
                                ]);

                                Notification::make()
                                    ->title('Congrats!')
                                    ->body('Hoorayy, you are now a member of this classroom!')
                                    ->success()
                                    ->send();

                                return redirect()->to('/classrooms'.'/'.Models\Classroomable::latest('id')->first()->id);
                            }
                        }
                    })
                    ->createAnother(false)
                    ->modalIcon($role === 1 ? 'heroicon-s-squares-plus' : 'heroicon-s-squares-2x2')
                    ->modalHeading($role === 1 ? 'Create Classroom' : 'Join Classroom')
                    ->modalDescription($role === 1 ? 'Create a classroom to make students join the classroom!' : 'Join an existing classroom using the token!')
                    ->modalSubmitActionLabel($role === 1 ? 'Create' : 'Join')
                    ->modalWidth('lg')
        ];
    }
}
