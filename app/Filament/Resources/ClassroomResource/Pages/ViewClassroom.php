<?php

namespace App\Filament\Resources\ClassroomResource\Pages;

use App\Models;
use App\Filament\Resources\ClassroomResource;
use Faker\Provider\Lorem;
use Filament\Forms;
use Filament\Actions;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
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

    public function getTitle(): string | Htmlable {   
        if($this->record) {
            if($this->record->classroom) {
                return $this->record->classroom->name;
            }
            if($this->record->name) {
                return $this->record->name;
            }
        }

        return __('filament-panels::resources/pages/view-record.title', [
            'label' => $this->getRecordTitle(),
        ]);
    }

    protected function getHeaderActions(): array {
        return [
            Actions\ActionGroup::make([
                Actions\EditAction::make()
                    ->visible(fn () => $this->role() === 1)
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
                Actions\Action::make($this->role() === 1 ? 'delete' : 'leave')
                    ->icon($this->role() === 1 ? 'heroicon-s-trash' : 'heroicon-s-arrow-left-start-on-rectangle')
                    ->label($this->role() === 1 ? 'Delete' : 'Leave')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading($this->role() === 1 ? 'Delete Classroom' : 'Leave Classroom')
                    ->modalDescription('Are you sure want to ' . ($this->role() === 1 ? 'delete' : 'leave') . ' this classroom? All data will be lost!')
                    ->action(function () {
                        if($this->role() === 1){
                            $this->record->delete();
                            Notifications\Notification::make()
                                ->title('Success!')
                                ->body('Classroom has been deleted!')
                                ->success()
                                ->send();
                        }else{
                            Models\Classroomable::where(['classroom_id' => $this->record->classroom_id,'classroomable_id' => auth()->guard()->user()->id])->delete();
                            Models\Answer::where('student_id', auth()->guard()->user()->id)->whereIn('topic_id', Models\Topic::where('classroom_id', $this->record->classroom_id)->pluck('id'))->delete();
                            Notifications\Notification::make()
                                ->title('Success!')
                                ->body('You have left this classroom!')
                                ->success()
                                ->send();
                        }
                        return redirect()->to('/classrooms');
                    })
            ])
            ->icon('heroicon-s-cog-8-tooth')
            ->size(Enums\ActionSize::ExtraLarge)
            ->color('info')
            ->iconButton()
        ];
    }

    public function infolist(Infolists\Infolist $infolist): Infolists\Infolist {
        return $infolist
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
                                        ->copyable()
                                        ->copyMessage('Token copied!')
                                        ->copyMessageDuration(2000)
                                        ->icon('heroicon-s-key')
                                        ->iconColor('warning')
                                        ->color('warning')
                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                        ->weight(Enums\FontWeight::Black)
                                        ->action(
                                            Infolists\Components\Actions\Action::make('view-token')
                                                ->requiresConfirmation()
                                                ->modalHeading($this->role() === 1 ? $this->record->token : $this->record->classroom->token)
                                                ->modalDescription('This is the token for this classroom')
                                                ->modalSubmitAction(false)
                                                ->modalCancelAction(false)
                                                ->modalIcon('heroicon-s-key')
                                                ->modalWidth('lg')
                                        ),
                                    ])
                                    ->visible(fn () => $this->role() === 1),
                                Infolists\Components\RepeatableEntry::make($this->role() === 1 ? 'topics' : 'classroom.topics')
                                    ->hiddenLabel()
                                    ->contained(false)
                                    ->schema([
                                        Infolists\Components\Section::make([
                                            Infolists\Components\TextEntry::make('title')
                                                ->hiddenLabel()
                                                ->icon('heroicon-s-document-text')
                                                ->iconColor('primary')
                                                ->weight(Enums\FontWeight::Bold)
                                                ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                            Infolists\Components\TextEntry::make('created_at')
                                                ->hiddenLabel()
                                                ->icon('heroicon-s-clock')
                                                ->alignEnd()
                                                ->size(Infolists\Components\TextEntry\TextEntrySize::ExtraSmall)
                                                ->since(),
                                            Infolists\Components\TextEntry::make('description')
                                                ->hiddenLabel()
                                                ->columnSpan(2)
                                                ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                            Infolists\Components\Actions::make([
                                                Infolists\Components\Actions\Action::make('file')
                                                    ->label(fn ($record) => Support\Str::replaceFirst(($this->role() === 1 ? $this->record->token : $this->record->classroom->token).'/','',$record->file))
                                                    ->icon('heroicon-s-document-arrow-down')
                                                    ->url(fn ($record) => '/storage/'.$record->file, true)
                                                    ->visible(fn ($record) => Support\Str::contains($record->file, ['.pdf', '.zip'])),
                                            ])
                                            ->columnSpan(2),
                                            Infolists\Components\ImageEntry::make('file')
                                                ->hiddenLabel()
                                                ->url(fn ($record) => '/storage/'.$record->file, true)
                                                ->size('100%')
                                                ->alignCenter()
                                                ->visible(fn ($record) => Support\Str::contains($record->file, ['.png', '.jpg', '.jpeg', '.gif']))
                                                ->columnSpan(2),
                                            Infolists\Components\ViewEntry::make('file')
                                                ->alignCenter()
                                                ->view('filament.infolists.entries.video-player')
                                                ->hiddenLabel()
                                                ->visible(fn ($record) => Support\Str::contains($record->file, ['.mp4']))
                                                ->columnSpan(2),
                                            Infolists\Components\ViewEntry::make('talk')
                                                ->alignCenter()
                                                ->view('filament.infolists.entries.disqus')
                                                ->hiddenLabel()
                                                ->columnSpan(2),
                                            Infolists\Components\Actions::make([
                                                Infolists\Components\Actions\Action::make('answer-topic')
                                                    ->hiddenLabel()
                                                    ->icon('heroicon-s-pencil')
                                                    ->color('primary')
                                                    ->form([
                                                        Forms\Components\Textarea::make('content')
                                                            ->hiddenLabel()
                                                            ->placeholder(Lorem::sentence(100))
                                                            ->required()
                                                            ->autosize()
                                                    ])
                                                    ->modalIcon('heroicon-s-pencil')
                                                    ->modalHeading('Answer the Topic!')
                                                    ->modalDescription('Tuliskan kesimpulan atau pendapatmu dari hasil diskusi pada kolom dibawah ini!')
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
                                                    }),
                                                ])
                                                ->fullWidth()
                                                ->columnSpan(2)
                                                ->visible(function ($record) {
                                                    $userId = auth()->guard()->user()->id;
                                                    return !$record->answers->where('student_id', $userId)->count() && $this->role() === 2;
                                                }),
                                            Infolists\Components\RepeatableEntry::make('answers')
                                                ->label('Answers')
                                                ->schema([
                                                    Infolists\Components\TextEntry::make('student.name')
                                                        ->hiddenLabel()
                                                        ->icon('heroicon-s-user')
                                                        ->iconColor('info')
                                                        ->size(Infolists\Components\TextEntry\TextEntrySize::ExtraSmall)
                                                        ->suffix(" (".$this->record->created_at->diffForHumans().")")
                                                        ->suffixActions([
                                                            Infolists\Components\Actions\Action::make('reply')
                                                                ->icon(fn ($record) => $record->reply ? 'heroicon-s-pencil' : 'heroicon-s-arrow-uturn-left')
                                                                ->iconButton()
                                                                ->hiddenLabel()
                                                                ->size(Enums\ActionSize::ExtraSmall)
                                                                ->visible($this->role() === 1)
                                                                ->fillForm(
                                                                    fn ($record) => ['reply' => $record->reply]
                                                                )
                                                                ->form([
                                                                    Forms\Components\Textarea::make('reply')
                                                                        ->hiddenLabel()
                                                                        ->placeholder(Lorem::sentence(100))
                                                                        ->required()
                                                                        ->autosize()
                                                                ])
                                                                ->modalIcon(fn ($record) => $record->reply === null ? 'heroicon-s-arrow-uturn-left' : 'heroicon-s-pencil')
                                                                ->modalHeading(fn ($record) => $record->reply === null ? 'Balas Jawaban!' : 'Edit Jawaban!')
                                                                ->modalDescription(fn ($record) => $record->reply === null ? 'Tuliskan balasan untuk jawaban tersebut disini!' : 'Edit balasan untuk jawaban tersebut disini!')
                                                                ->action(function (array $data, $record){
                                                                    if(
                                                                        Models\Answer::where('id', $record->id)->update([
                                                                            'reply' => $data['reply'],
                                                                            'reply_from' => auth()->guard()->user()->id,
                                                                        ])
                                                                    ){
                                                                        Notifications\Notification::make()
                                                                            ->title('Success!')
                                                                            ->body('Reply has been sent!')
                                                                            ->success()
                                                                            ->send();
                                                                        return;
                                                                    }
                                                                }),
                                                            Infolists\Components\Actions\Action::make('edit')
                                                                ->icon('heroicon-s-pencil-square')
                                                                ->iconButton()
                                                                ->hiddenLabel()
                                                                ->color('warning')
                                                                ->size(Enums\ActionSize::ExtraSmall)
                                                                ->fillForm(
                                                                    fn ($record) => ['content' => $record->content]
                                                                )
                                                                ->form([
                                                                    Forms\Components\Textarea::make('content')
                                                                        ->hiddenLabel()
                                                                        ->required()
                                                                        ->placeholder(Lorem::sentence(100))
                                                                        ->autosize(),
                                                                ])
                                                                ->action(function (array $data,$record) {
                                                                    Models\Answer::where('id', $record->id)->update([
                                                                        'content' => $data['content']
                                                                    ]);
                
                                                                    return Notifications\Notification::make()
                                                                        ->title('Congrats!')
                                                                        ->body('Answer has been changed!')
                                                                        ->success()
                                                                        ->send();
                                                                })
                                                                ->modalIcon('heroicon-s-pencil-square')
                                                                ->modalHeading('Edit Answer')
                                                                ->modalDescription('Edit this answer!')
                                                                ->modalWidth('2xl')
                                                                ->visible(fn ($record) => auth()->guard()->user()->id === $record->student_id),
                                                            Infolists\Components\Actions\Action::make($this->role() === 1 ? 'delete-reply' : 'delete-answer')
                                                                ->icon('heroicon-s-trash')
                                                                ->iconButton()
                                                                ->hiddenLabel()
                                                                ->color('danger')
                                                                ->size(Enums\ActionSize::ExtraSmall)
                                                                ->action(function ($record) {
                                                                    if($this->role() === 1){
                                                                        Models\Answer::where('id', $record->id)->update([
                                                                            'reply' => null,
                                                                            'reply_from' => null,
                                                                        ]);
                                                                    }else{
                                                                        Models\Answer::where('id', $record->id)->delete();
                                                                    }
                                                                    return Notifications\Notification::make()
                                                                        ->title('Success!')
                                                                        ->body($this->role() === 1 ? 'Reply has been deleted!' : 'Answer has been deleted!')
                                                                        ->success()
                                                                        ->send();
                                                                })
                                                                ->requiresConfirmation()
                                                                ->modalHeading($this->role() === 1 ? 'Delete Reply' : 'Delete Answer')
                                                                ->modalDescription('Are you sure want to delete this '. ($this->role() === 1 ? 'reply' : 'answer').'?')
                                                                ->modalWidth('lg')
                                                                ->visible(fn ($record) => $this->role() === 2 || $record->reply !== null),
                                                        ]),
                                                    Infolists\Components\TextEntry::make('content')
                                                        ->hiddenLabel()
                                                        ->icon('heroicon-s-chat-bubble-left-ellipsis')
                                                        ->iconColor('info')
                                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                                    Infolists\Components\TextEntry::make('reply')
                                                        ->label('Reply from Teacher')
                                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Medium)
                                                        ->icon('heroicon-s-arrow-turn-down-right')
                                                        ->visible(fn ($record) => $record->reply)
                                                ])
                                                ->columnSpan(2)
                                                ->visible(fn ($record) => $record->answers->count() > 0)
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
                                            Forms\Components\Textarea::make('description')
                                                ->label('Topic Description')
                                                ->required()
                                                ->maxLength(255)
                                                ->placeholder(Lorem::sentence(50))
                                                ->autosize(),
                                            Forms\Components\FileUpload::make('file')
                                                ->label('File')                                                        
                                                ->required()
                                                ->maxSize(8192)
                                                ->preserveFilenames()
                                                ->directory($this->role() === 1 ? $this->record->token : $this->record->classroom->token)
                                                ->acceptedFileTypes([
                                                    'application/pdf',
                                                    'application/zip', 'application/x-compressed', 'application/x-zip-compressed', 'multipart/x-zip',
                                                    'image/png', 'image/jpg', 'image/jpeg', 'image/gif',
                                                    'video/mp4'
                                                ])
                                        ])
                                        ->action(function (array $data) {
                                            Models\Topic::create([
                                                'title' => $data['title'],
                                                'description' => $data['description'],
                                                'file' => $data['file'],
                                                'classroom_id' => $this->record->id,
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
                                ->fullWidth()
                                ->visible(fn () => $this->role() === 1),
                                Infolists\Components\RepeatableEntry::make(($this->role() === 2 ? 'classroom.' : '').'topics')
                                    ->hiddenLabel()
                                    ->schema([
                                        Infolists\Components\TextEntry::make('title')
                                            ->hiddenLabel()
                                            ->icon('heroicon-s-document-text')
                                            ->iconColor('info')
                                            ->weight(Enums\FontWeight::Bold)
                                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                        Infolists\Components\TextEntry::make('created_at')
                                            ->hiddenLabel()
                                            ->icon('heroicon-s-clock')     
                                            ->size(Infolists\Components\TextEntry\TextEntrySize::ExtraSmall)
                                            ->alignEnd()
                                            ->since()
                                            ->visible(fn () => $this->role() === 2),
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
                                                    Forms\Components\Textarea::make('description')
                                                        ->label('Topic Description')
                                                        ->required()
                                                        ->maxLength(255)
                                                        ->placeholder(Lorem::sentence(50))
                                                        ->autosize(),
                                                    Forms\Components\FileUpload::make('file')
                                                        ->label('File')                                                        
                                                        ->required()
                                                        ->maxSize(5120)
                                                        ->preserveFilenames()
                                                        ->directory($this->role() === 1 ? $this->record->token : $this->record->classroom->token)
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
                                            ->visible(fn () => $this->role() === 1)
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
                                                    ->body('Don\'t worry, '.$student->name.' is already a student of this classroom!')
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
                                                    ->body('Hoorayy, '.$student->name.' is now a student of this classroom!')
                                                    ->success()
                                                    ->send();
                                            }
                                        }),
                                ])
                                ->fullWidth()
                                ->visible(fn () => $this->role() === 1),
                                Infolists\Components\RepeatableEntry::make(($this->role() === 2 ? 'classroom.' : '').'students')
                                    ->hiddenLabel()    
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->hiddenLabel()
                                            ->icon('heroicon-s-user')
                                            ->iconColor('info')
                                            ->weight(Enums\FontWeight::Bold),
                                        Infolists\Components\TextEntry::make('email')
                                            ->hiddenLabel()
                                            ->icon('heroicon-s-envelope')
                                            ->iconColor('info')
                                            ->visible(fn () => $this->role() === 1),
                                    ])
                                    ->columns(2)
                            ])
                    ])
                    ->columnSpan(2)
                    ->persistTab()
                    ->id('classroom-tabs')
            ]);
    }

    protected function role(): int {
        return auth()->guard()->user()->role;
    }
}
