<?php

namespace App\Filament\Resources\ClassroomResource\Pages;

use App\Filament\Resources\ClassroomResource;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Form;

class CreateClassroom extends CreateRecord
{
    protected static string $resource = ClassroomResource::class;
    
    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('token')
                ->readOnly()
                ->hidden()
                ->required()
                ->unique()
                ->maxLength(255)
                ->placeholder('Classroom Token'),
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('Classroom Name'),
            TextInput::make('subject')
                ->maxLength(255)
                ->placeholder('Subject'),
        ]);
    }
}
