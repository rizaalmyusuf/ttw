<?php

namespace App\Filament\Resources;

use App\Models\User;
use App\Filament\Resources\UserResource\Pages\ManageUsers;
use App\Filament\Widgets\StatsOverview;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $activeNavigationIcon = 'heroicon-s-users';
    
    public static function getEloquentQuery(): Builder
    {
        if (auth()->guard('web')->user()->role == 0) {
            return parent::getEloquentQuery()->where('role', '!=', 0);
        } else {
            return parent::getEloquentQuery()->where('role', 2);
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('username')
                    ->placeholder('johndoe')
                    ->required()
                    ->maxLength(255),
                TextInput::make('name')
                    ->label('Full Name')
                    ->placeholder('John Doe')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->placeholder('johndoe@mail.com')
                    ->required()
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->placeholder('Type your password here')
                    ->required()
                    ->maxLength(255),
                Select::make('role')
                    ->options([
                        1 => 'Teacher',
                        2 => 'Student',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied to clipboard'),
                TextColumn::make('role')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        0 => 'primary',
                        1 => 'info',
                        2 => 'success',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        0 => 'Admin',
                        1 => 'Teacher',
                        2 => 'Student',
                    }),
                IconColumn::make('status')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }
}
