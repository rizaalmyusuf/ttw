<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Forms\Form;
use Filament\Actions\CreateAction;
use function Laravel\Prompts\form;
use App\Filament\Resources\UserResource;

use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ManageRecords;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon('heroicon-s-user-plus')
            ];
    }
}
