<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use App\Filament\Admin\Resources\UserResource;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ManageRecords;


class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    // protected static ?string $model = User::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-s-user-plus')
                ->form([
                    TextInput::make('username')->placeholder('johndoe')->required()->maxLength(255),
                    TextInput::make('name')->label('Full Name')->placeholder('John Doe')->required()->maxLength(255),
                    TextInput::make('email')->email()->placeholder('johndoe@mail.com')->required()->maxLength(255),
                    TextInput::make('password')->password()->placeholder('Type your password here')->required()->maxLength(255),
                    Select::make('role')->options(['1' => 'Teacher','2' => 'Student'])->required(),
                ])
            ];
    }     
}
