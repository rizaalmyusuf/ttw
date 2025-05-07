<?php

namespace App\Filament\Resources\ClassroomResource\Pages;

use App\Filament\Resources\ClassroomResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Support\Enums\ActionSize;
use Illuminate\Contracts\Support\Htmlable;

class ViewClassroom extends ViewRecord
{
    use InteractsWithRecord;

    protected static string $resource = ClassroomResource::class;

    protected static ?string $title = null;

    public function getTitle(): string | Htmlable
    {   
        if($this->record->getAttributes()) {
            return $this->record->getAttributes()['name'];
        }

        return __('filament-panels::resources/pages/view-record.title', [
            'label' => $this->getRecordTitle(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        if(auth()->guard('web')->user()->role === 1) {
            return [
                Actions\ActionGroup::make([
                    Actions\EditAction::make()
                        ->icon('heroicon-s-pencil-square')
                        ->label('Edit')
                        ->color('warning')
                        ->form([
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
                        ])
                        ->modalIcon('heroicon-s-pencil-square')
                        ->modalHeading('Edit Classroom'),
                    Actions\DeleteAction::make()
                        ->icon('heroicon-s-trash')
                        ->label('Delete')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalIcon('heroicon-s-trash')
                        ->modalHeading('Delete Classroom')
                        ->modalSubheading('Are you sure you want to delete this classroom?')
                ])
                ->icon('heroicon-s-cog-8-tooth')
                ->size(ActionSize::Large)
                ->color('info')
                ->iconButton()
            ];
        }

        return [];
    }

    public function infolist(Infolists\Infolist $infolist): Infolists\Infolist
    {
        if (auth()->guard('web')->user()->role === 1) {


            return
                $infolist                   
                    ->name('Classroom')
                    ->schema([
                        Infolists\Components\Tabs::make('Tabs')
                            ->tabs([
                                Infolists\Components\Tabs\Tab::make('Stream')
                                    ->icon('heroicon-s-signal')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('token')
                                            ->label('Token')
                                            ->badge()
                                            ->color('warning')
                                            ->icon('heroicon-s-key'),
                                    ]),
                                Infolists\Components\Tabs\Tab::make('Topic Works')
                                    ->icon('heroicon-s-clipboard-document-list')
                                    ->schema([
                                        
                                    ]),
                                Infolists\Components\Tabs\Tab::make('Students')
                                    ->icon('heroicon-s-users')
                                    ->schema([
                                        
                                    ])
                            ])
                        
                    ]);
        }

        return $infolist;
    }

}
