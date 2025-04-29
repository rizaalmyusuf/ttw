<?php

namespace App\Filament\Resources\ClassroomResource\Pages;

use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\ClassroomResource;
use Filament\Actions\Concerns\HasForm;
use Filament\Forms\Form;

class CreateClassroom extends CreateRecord
{
    protected static string $resource = ClassroomResource::class;
    
    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('Classroom Name'),
        ]);
    }
}
