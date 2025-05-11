<?php

namespace App\Filament\Resources\ClassroomResource\Pages;

use Filament\Forms;
use Filament\Tables;
use App\Models\Topic;
use Filament\Actions;
use Filament\Infolists;
use Faker\Provider\Lorem;
use Filament\Notifications;
use Illuminate\Support\Str;
use PhpParser\Node\Stmt\Echo_;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Storage;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\ClassroomResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Support\Enums\Alignment;

class ViewClassroom extends ViewRecord
{
    use InteractsWithRecord;

    protected static string $resource = ClassroomResource::class;

    protected static ?string $title = null;

    public function getTitle(): string | Htmlable
    {   
        if($this->record) {
            if($this->record->classroom) {
                return $this->record->classroom->name;
            }
            if($this->record->getAttributes()['name']) {
                return $this->record->getAttributes()['name'];
            }
            // return $this->record->getAttributes()['name'];
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
                            Forms\Components\TextInput::make('name')
                                ->label('Classroom Name')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Classroom Name')
                                ->autofocus(),
                            Forms\Components\TextInput::make('subject')
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
                        ->modalHeading('Delete Classroom')
                        ->modalDescription('Are you sure want to delete this classroom?')
                        ->action(function () {
                            $this->record->delete();

                            Notifications\Notification::make()
                                ->title('Classroom Deleted')
                                ->success()
                                ->send();
                            
                            return redirect()->to('/classrooms');
                        })
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
                                            ->icon('heroicon-s-key')
                                            ->action(
                                                Infolists\Components\Actions\Action::make('view-token')
                                                    ->requiresConfirmation()
                                                    ->modalHeading($this->record->getAttributes()['token'])
                                                    ->modalDescription('This is the token for this classroom')
                                                    ->modalSubmitAction(false)
                                                    ->modalCancelAction(false)
                                                    ->modalIcon('heroicon-s-key')
                                                    ->modalWidth('lg')
                                            ),
                                            Infolists\Components\RepeatableEntry::make('topics')
                                                ->contained(false)
                                                ->schema([
                                                    Infolists\Components\Section::make([
                                                        Infolists\Components\TextEntry::make('title')
                                                            ->label('')
                                                            ->icon('heroicon-s-document-text')
                                                            ->weight(FontWeight::Bold),
                                                        Infolists\Components\TextEntry::make('created_at')
                                                            ->label('')
                                                            ->icon('heroicon-s-clock')
                                                            ->since()
                                                            ->alignEnd(),
                                                        Infolists\Components\Section::make([
                                                            Infolists\Components\TextEntry::make('description')
                                                                ->label(''),
                                                            Infolists\Components\Actions::make([
                                                                Infolists\Components\Actions\Action::make('file')
                                                                    ->label(fn ($record) => Str::replaceFirst($this->record->getAttributes()['token'].'/','',$record->file))
                                                                    ->icon('heroicon-s-document-arrow-down')
                                                                    ->url(fn ($record) => '/storage/'.$record->file, true),
                                                                ]),
                                                            Infolists\Components\Section::make('Answers')
                                                                ->schema([
                                                                    
                                                                ])
                                                                ->collapsed()

                                                        ])
                                                    ])
                                                    ->columns(2)
                                                ])
                                    ]),
                                Infolists\Components\Tabs\Tab::make('Topic Works')
                                    ->icon('heroicon-s-clipboard-document-list')
                                    ->schema([
                                        Infolists\Components\Actions::make([
                                            Infolists\Components\Actions\Action::make('createTopic')
                                                ->icon('heroicon-s-plus')
                                                ->label('Add Topic')
                                                ->requiresConfirmation()
                                                ->form([
                                                    Forms\Components\TextInput::make('title')
                                                        ->label('Topic Title')
                                                        ->required()
                                                        ->maxLength(255)
                                                        ->placeholder('Programming Language')
                                                        ->autocapitalize('words'),
                                                    Forms\Components\TextInput::make('description')
                                                        ->label('Topic Description')
                                                        ->required()
                                                        ->maxLength(255)
                                                        ->placeholder(Lorem::sentence(10)),
                                                    Forms\Components\FileUpload::make('file')
                                                        ->label('File')                                                        
                                                        ->required()
                                                        ->maxSize(5120)
                                                        ->preserveFilenames()
                                                        ->directory($this->record->getAttributes()['token'])
                                                        ->acceptedFileTypes(['application/pdf', 'image/*'])
                                                ])
                                                ->action(function (array $data) {
                                                    Topic::create([
                                                        'title' => $data['title'],
                                                        'description' => $data['description'],
                                                        'file' => $data['file'],
                                                        'classroom_id' => $this->record->getAttributes()['id'],
                                                    ]);

                                                    return Notifications\Notification::make()
                                                        ->title('Topic Created')
                                                        ->success()
                                                        ->send();
                                                })
                                                ->modalIcon('heroicon-s-plus')
                                                ->modalHeading('Add Topic')
                                                ->modalDescription('Add a new topic to this classroom!')
                                                ->modalWidth('2xl')
                                        ])
                                        ->fullWidth(),
                                        Infolists\Components\RepeatableEntry::make('topics')
                                            ->label('')
                                            ->schema([
                                                Infolists\Components\TextEntry::make('title')
                                                    ->label('')
                                                    ->icon('heroicon-s-document-text')
                                                    ->weight(FontWeight::Bold),
                                                Infolists\Components\Actions::make([
                                                    Infolists\Components\Actions\Action::make('delete')
                                                        ->icon('heroicon-s-trash')
                                                        ->color('danger')
                                                        ->action(function ($record) {
                                                            Topic::where('id', $record->id)->delete();
                                                            Storage::delete($record->file);
                                                            return Notifications\Notification::make()
                                                                ->title('Topic Deleted.')
                                                                ->success()
                                                                ->send();
                                                        })
                                                        ->requiresConfirmation()
                                                        ->modalHeading('Delete Topic')
                                                        ->modalDescription('Are you sure want to delete this topic?')
                                                        ->modalWidth('lg'),
                                                    Infolists\Components\Actions\Action::make('edit')
                                                        ->icon('heroicon-s-pencil-square')
                                                        ->color('warning')
                                                        ->fillForm(
                                                            fn ($record) => [
                                                                'title' => $record->title,
                                                                'description' => $record->description,
                                                                'file' => $record->file,
                                                            ]
                                                        )
                                                        ->form([
                                                            Forms\Components\TextInput::make('title')
                                                                ->label('Topic Title')
                                                                ->required()
                                                                ->maxLength(255)
                                                                ->placeholder('Programming Language')
                                                                ->autocapitalize('words'),
                                                            Forms\Components\TextInput::make('description')
                                                                ->label('Topic Description')
                                                                ->required()
                                                                ->maxLength(255)
                                                                ->placeholder(Lorem::sentence(10)),
                                                            Forms\Components\FileUpload::make('file')
                                                                ->label('File')                                                        
                                                                ->required()
                                                                ->maxSize(5120)
                                                                ->preserveFilenames()
                                                                ->directory($this->record->getAttributes()['token'])
                                                                ->acceptedFileTypes(['application/pdf', 'image/*']),
                                                        ])
                                                        ->action(function (array $data,$record) {
                                                            Topic::where('id', $record->id)->update([
                                                                'title' => $data['title'],
                                                                'description' => $data['description'],
                                                                'file' => $data['file'],
                                                            ]);

                                                            return Notifications\Notification::make()
                                                                ->title('Topic Updated.')
                                                                ->success()
                                                                ->send();
                                                        })
                                                        ->modalIcon('heroicon-s-pencil-square')
                                                        ->modalHeading('Edit Topic')
                                                        ->modalDescription('Edit this topic!')
                                                        ->modalWidth('2xl'),
                                                    ])
                                                    ->alignEnd()
                                            ])
                                            ->columns(2)
                                    ]),
                                Infolists\Components\Tabs\Tab::make('Students')
                                    ->icon('heroicon-s-users')
                                    ->schema([
                                        Infolists\Components\RepeatableEntry::make('students')
                                            ->label('')    
                                            ->schema([
                                                Infolists\Components\TextEntry::make('name')
                                                    ->label('Name')
                                                    ->icon('heroicon-s-user')
                                                    ->weight(FontWeight::Bold),
                                                Infolists\Components\TextEntry::make('email')
                                                    ->label('Email')
                                                    ->icon('heroicon-s-envelope'),
                                            ])
                                            ->columns(2)
                                    ])
                            ])
                            ->columnSpan(2)
                            // ->persistTabInQueryString()
                            ->persistTab()
                            ->id('classroom-tabs')
                    ]);
        }else{
            return
                $infolist
                    ->name('Classroom')
                    ->schema([
                        Infolists\Components\Tabs::make('Tabs')
                            ->tabs([
                                Infolists\Components\Tabs\Tab::make('Topic Works')
                                    ->icon('heroicon-s-clipboard-document-list')
                                    ->schema([
                                        
                                    ]),
                                // Infolists\Components\Tabs\Tab::make('Classmates')
                                //     ->icon('heroicon-s-users')
                                //     ->schema([
                                //         Infolists\Components\RepeatableEntry::make('students')
                                //             ->label('')    
                                //             ->schema([
                                //                 Infolists\Components\TextEntry::make('name')
                                //                     ->label('Name')
                                //                     ->icon('heroicon-s-user'),
                                //             ])
                                //     ])
                            ])
                            ->columnSpan(2)
                        
                    ]);
        }
        return $infolist;
    }

}
