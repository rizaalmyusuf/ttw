<?php

namespace App\Filament\Resources\ClassroomResource\Pages;

use App\Models;
use App\Filament\Resources\ClassroomResource;
use Faker\Provider\Lorem;
use Filament\Forms;
use Filament\Tables;
use Filament\Actions;
use Filament\Infolists;
use Filament\Notifications;
use Filament\Support\Enums;
use Filament\Resources\Pages;
use Illuminate\Support;
use Illuminate\Contracts\Support\Htmlable;

class ViewClassroom extends Pages\ViewRecord
{
    use Pages\Concerns\InteractsWithRecord;

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
        if(auth()->guard()->user()->role === 1) {
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
                                ->title('Success!')
                                ->body('Classroom has been deleted!')
                                ->success()
                                ->send();
                            
                            return redirect()->to('/classrooms');
                        })
                ])
                ->icon('heroicon-s-cog-8-tooth')
                ->size(Enums\ActionSize::Large)
                ->color('info')
                ->iconButton()
            ];
        }

        return [];
    }

    public function infolist(Infolists\Infolist $infolist): Infolists\Infolist
    {
        if (auth()->guard()->user()->role === 1) {
            return
                $infolist                   
                    ->name('Classroom')
                    ->schema([
                        Infolists\Components\Tabs::make('Tabs')
                            ->tabs([
                                Infolists\Components\Tabs\Tab::make('Stream')
                                    ->icon('heroicon-s-signal')
                                    ->schema([
                                        Infolists\Components\Section::make([
                                            Infolists\Components\TextEntry::make('token')
                                                ->label('Token')
                                                // ->badge()
                                                ->icon('heroicon-s-key')
                                                ->iconColor('warning')
                                                ->color('warning')
                                                ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                                ->weight(Enums\FontWeight::Black)
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
                                            ]),
                                        Infolists\Components\RepeatableEntry::make('topics')
                                            ->label('')
                                            ->contained(false)
                                            ->schema([
                                                Infolists\Components\Section::make([
                                                    Infolists\Components\TextEntry::make('title')
                                                        ->label('')
                                                        ->icon('heroicon-s-document-text')
                                                        ->iconColor('primary')
                                                        ->weight(Enums\FontWeight::Bold)
                                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                                    Infolists\Components\TextEntry::make('created_at')
                                                        ->label('')
                                                        ->icon('heroicon-s-clock')
                                                        ->alignEnd()
                                                        ->size(Infolists\Components\TextEntry\TextEntrySize::ExtraSmall)
                                                        ->since(),
                                                    Infolists\Components\Section::make([
                                                        Infolists\Components\TextEntry::make('description')
                                                            ->label(''),
                                                        Infolists\Components\Actions::make([
                                                            Infolists\Components\Actions\Action::make('file')
                                                                ->label(fn ($record) => Support\Str::replaceFirst($this->record->getAttributes()['token'].'/','',$record->file))
                                                                ->icon('heroicon-s-document-arrow-down')
                                                                ->url(fn ($record) => '/storage/'.$record->file, true),
                                                            ]),
                                                        Infolists\Components\Section::make('Answers')
                                                            ->schema([
                                                                Infolists\Components\RepeatableEntry::make('answers')
                                                                ->label('')
                                                                ->schema([
                                                                    Infolists\Components\TextEntry::make('student.name')
                                                                        ->label('')
                                                                        ->icon('heroicon-s-user')
                                                                        ->iconColor('info'),
                                                                    Infolists\Components\TextEntry::make('content')
                                                                        ->label('')                                                                       
                                                                ])
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
                                                ->icon('heroicon-s-document-plus')
                                                ->label('Add Topic')
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
                                                    Models\Topic::create([
                                                        'title' => $data['title'],
                                                        'description' => $data['description'],
                                                        'file' => $data['file'],
                                                        'classroom_id' => $this->record->getAttributes()['id'],
                                                    ]);

                                                    return Notifications\Notification::make()
                                                        ->title('Congrats!')
                                                        ->body('Topic has been created!')
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
                                                    ->iconColor('info')
                                                    ->weight(Enums\FontWeight::Bold)
                                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                                Infolists\Components\Actions::make([
                                                    Infolists\Components\Actions\Action::make('delete')
                                                        ->icon('heroicon-s-trash')
                                                        ->color('danger')
                                                        ->action(function ($record) {
                                                            Models\Topic::where('id', $record->id)->delete();
                                                            Support\Facades\Storage::delete($record->file);
                                                            return Notifications\Notification::make()
                                                                ->title('Success!')
                                                                ->body('Topic has been deleted!')
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
                                                            Models\Topic::where('id', $record->id)->update([
                                                                'title' => $data['title'],
                                                                'description' => $data['description'],
                                                                'file' => $data['file'],
                                                            ]);

                                                            return Notifications\Notification::make()
                                                                ->title('Congrats!')
                                                                ->body('Topic has been updated!')
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
                                        Infolists\Components\Actions::make([
                                            Infolists\Components\Actions\Action::make('inviteStudent')
                                                ->label('Invite Student')
                                                ->icon('heroicon-s-user-plus')
                                                ->modalIcon('heroicon-s-user-plus')
                                                ->modalHeading('Invite Student')
                                                ->modalDescription('Invite people to reach more student')
                                                ->form([
                                                    Forms\Components\TextInput::make('search')
                                                        ->label('Username or Email')
                                                        ->placeholder('\'muridtik\' or \'muridtik@ttw.id\'')
                                                        ->required()
                                                ])
                                                ->action(function (array $data, $record){
                                                    if(Models\User::where('username', $data['search'])->first()){
                                                        $student = Models\User::where('username', $data['search'])->first();
                                                    }elseif (Models\User::where('email', $data['search'])->first()){
                                                        $student = Models\User::where('email', $data['search'])->first();
                                                    }else{
                                                        return Notifications\Notification::make()
                                                            ->title('Failed!')
                                                            ->body('Ouh no, people not found!')
                                                            ->danger()
                                                            ->send();
                                                    };

                                                    if(Models\Classroomable::where(['classroom_id' => $record->id,'classroomable_id' => $student->id])->first()){
                                                        return Notifications\Notification::make()
                                                            ->title('Failed!')
                                                            ->body('Don\'t worry, '.$student->name.' is already a member of this classroom!')
                                                            ->warning()
                                                            ->send();
                                                    }else{
                                                        Models\Classroomable::create([
                                                            'classroom_id' => $record->id,
                                                            'classroomable_id' => $student->id,
                                                            'classroomable_type' => 'App\Models\User'
                                                        ]);

                                                        return Notifications\Notification::make()
                                                            ->title('Congrats!')
                                                            ->body('Hoorayy, '.$student->name.' is now a member of this classroom!')
                                                            ->success()
                                                            ->send();
                                                    }
                                                }),
                                        ])
                                        ->fullWidth(),
                                        Infolists\Components\RepeatableEntry::make('students')
                                            ->label('')    
                                            ->schema([
                                                Infolists\Components\TextEntry::make('name')
                                                    ->label('')
                                                    ->icon('heroicon-s-user')
                                                    ->iconColor('info')
                                                    ->weight(Enums\FontWeight::Bold),
                                                Infolists\Components\TextEntry::make('email')
                                                    ->label('')
                                                    ->icon('heroicon-s-envelope')
                                                    ->iconColor('info'),
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
                                Infolists\Components\Tabs\Tab::make('Stream')
                                    ->icon('heroicon-s-signal')
                                    ->schema([
                                        Infolists\Components\RepeatableEntry::make('classroom.topics')
                                            ->label('')
                                            ->contained(false)
                                            ->schema([
                                                Infolists\Components\Section::make([
                                                    Infolists\Components\TextEntry::make('title')
                                                        ->label('')
                                                        ->icon('heroicon-s-document-text')
                                                        ->iconColor('primary')
                                                        ->weight(Enums\FontWeight::Bold)
                                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                                    Infolists\Components\TextEntry::make('created_at')
                                                        ->label('')
                                                        ->icon('heroicon-s-clock')
                                                        ->since()
                                                        ->alignEnd()
                                                        ->size(Infolists\Components\TextEntry\TextEntrySize::ExtraSmall),
                                                    Infolists\Components\Section::make([
                                                        Infolists\Components\TextEntry::make('description')
                                                            ->label(''),
                                                        Infolists\Components\Actions::make([
                                                            Infolists\Components\Actions\Action::make('file')
                                                                ->label(fn ($record) => Support\Str::replaceFirst($this->record->classroom->getAttributes()['token'].'/','',$record->file))
                                                                ->icon('heroicon-s-document-arrow-down')
                                                                ->url(fn ($record) => '/storage/'.$record->file, true),
                                                            ]),
                                                        ]),
                                                    Infolists\Components\Section::make('Answers')
                                                        ->headerActions([                                                            
                                                            Infolists\Components\Actions\Action::make('answer-topic')
                                                                ->label('')
                                                                ->icon('heroicon-s-pencil')
                                                                ->color('warning')
                                                                ->form([
                                                                    Forms\Components\TextInput::make('content')
                                                                        ->label('')
                                                                        ->placeholder('Type your answer here!')
                                                                        ->required()
                                                                ])
                                                                ->modalIcon('heroicon-s-pencil')
                                                                ->modalHeading('Answer the Topic!')
                                                                ->modalDescription('Remember! Your answer is cannot be undone or edit.')
                                                                ->action(function (array $data, $record){
                                                                    if(
                                                                        Models\Answer::create([
                                                                            'content' => $data['content'],
                                                                            'topic_id' => $record->id,
                                                                            'student_id' => auth()->guard()->user()->id,
                                                                    ])){
                                                                        Notifications\Notification::make()
                                                                            ->title('Congrats!')
                                                                            ->body('Your answer has been submitted!')
                                                                            ->success()
                                                                            ->send();
                                                                        return;
                                                                    }
                                                                })
                                                        ])
                                                        ->schema([
                                                            Infolists\Components\RepeatableEntry::make('answers')
                                                                ->label('')
                                                                ->schema([
                                                                    Infolists\Components\TextEntry::make('student.name')
                                                                        ->label('')
                                                                        ->icon('heroicon-s-user'),
                                                                    Infolists\Components\TextEntry::make('content')
                                                                        ->label('')                                                                       
                                                                ])
                                                        ])
                                                        ->collapsed()
                                                ])
                                                ->columns(2)
                                            ])
                                    ]),
                                Infolists\Components\Tabs\Tab::make('Topic Works')
                                    ->icon('heroicon-s-clipboard-document-list')
                                    ->schema([
                                        Infolists\Components\RepeatableEntry::make('classroom.topics')
                                            ->label('')
                                            ->schema([
                                                Infolists\Components\TextEntry::make('title')
                                                    ->label('')
                                                    ->icon('heroicon-s-document-text')
                                                    ->iconColor('info')
                                                    ->weight(Enums\FontWeight::Bold)
                                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                                Infolists\Components\TextEntry::make('created_at')
                                                    ->label('')
                                                    ->icon('heroicon-s-clock')     
                                                    ->size(Infolists\Components\TextEntry\TextEntrySize::ExtraSmall)
                                                    ->alignEnd()
                                                    ->since(),
                                            ])
                                            ->columns(2)
                                    ]),
                                Infolists\Components\Tabs\Tab::make('Students')
                                    ->icon('heroicon-s-users')
                                    ->schema([
                                        Infolists\Components\RepeatableEntry::make('classroom.students')
                                            ->label('')
                                            ->schema([
                                                Infolists\Components\TextEntry::make('name')
                                                    ->label('')
                                                    ->icon('heroicon-s-user')
                                                    ->iconColor('info')
                                                    ->weight(Enums\FontWeight::Bold)
                                            ])
                                            ->columns(2)
                                    ])
                            ])
                            ->columnSpan(2)
                            ->persistTab()
                            ->id('classroom-tabs')
                    ]);
        }
        return $infolist;
    }

}
