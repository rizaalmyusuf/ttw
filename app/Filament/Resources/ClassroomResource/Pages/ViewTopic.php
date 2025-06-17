<?php

namespace App\Filament\Resources\ClassroomResource\Pages;

use App\Models;
use Filament\Forms;
use Filament\Infolists;
use Illuminate\Support;
use Faker\Provider\Lorem;
use Filament\Notifications;
use Filament\Support\Enums;
use Filament\Resources\Pages;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\ClassroomResource;

// class ViewTopic extends Pages\ViewRecord
// class ViewTopic extends ClassroomResource\Pages\ViewClassroom
class ViewTopic extends Pages\Page
{
    // use Pages\Concerns\InteractsWithRecord;

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

    protected static string $view = 'filament.resources.classroom-resource.pages.view-topic';

    public function infolist(Infolists\Infolist $infolist): Infolists\Infolist {
        return $infolist
            ->name('Classroom')
            ->schema([
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
                            Infolists\Components\Actions::make([
                                Infolists\Components\Actions\Action::make('talk')
                                    ->hiddenLabel()
                                    ->icon('heroicon-s-chat-bubble-left-right')
                                    ->color('primary')
                                    ->url(fn ($record) => '/classrooms/topic/'.$record->id)
                                ])
                                ->fullWidth()
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
                    ])
                    ->columnSpan(2),
            ]);
    }

    protected function role(): int {
        return auth()->guard()->user()->role;
    }
}
