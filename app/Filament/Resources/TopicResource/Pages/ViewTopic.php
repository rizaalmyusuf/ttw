<?php

namespace App\Filament\Resources\TopicResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\TopicResource;
use Illuminate\Contracts\Support\Htmlable;
use App\Models;
use Filament\Forms;
use Filament\Infolists;
use Illuminate\Support;
use Faker\Provider\Lorem;
use Filament\Notifications;
use Filament\Support\Enums;
use Filament\Resources\Pages;
use App\Filament\Resources\ClassroomResource;
use Filament\Actions\Concerns\HasInfolist;

class ViewTopic extends ViewRecord
{
    use Pages\Concerns\InteractsWithRecord;

    protected static string $resource = TopicResource::class;

    protected static ?string $breadcrumb = 'Discussion';

    protected static ?string $title = null;

    protected static string $view = 'filament.resources.topic-resource.pages.view-topic';

    public function getTitle(): string | Htmlable {
        return $this->record->title ?? __('filament-panels::resources/pages/view-record.title', [
            'label' => $this->getRecordTitle(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backToClassroom')
                ->label('Back to Classroom')
                ->icon('heroicon-s-arrow-left')
                ->url('/classrooms/'.($this->role() === 1 ? $this->record->classroom_id : Models\Classroomable::where('classroomable_id', auth()->guard()->user()->id)->first()->id)),
        ];
    }

    public function infolist(Infolists\Infolist $infolist): Infolists\Infolist {
        return $infolist
            ->name('Classroom')
            ->schema([
                Infolists\Components\TextEntry::make('description')
                    ->hiddenLabel()
                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                    ->markdown(),
                Infolists\Components\Actions::make([
                    Infolists\Components\Actions\Action::make('file')
                        ->label(fn ($record) => Support\Str::replaceFirst(($this->role() === 1 ? $this->record->token : $this->record->classroom->token).'/','',$record->file))
                        ->icon('heroicon-s-document-arrow-down')
                        ->url(fn ($record) => '/storage/'.$record->file, true)
                        ->visible(fn ($record) => Support\Str::contains($record->file, ['.pdf', '.zip'])),
                ]),
                Infolists\Components\ImageEntry::make('file')
                    ->hiddenLabel()
                    ->url(fn ($record) => '/storage/'.$record->file, true)
                    ->size('100%')
                    ->alignCenter()
                    ->visible(fn ($record) => Support\Str::contains($record->file, ['.png', '.jpg', '.jpeg', '.gif'])),
                Infolists\Components\ViewEntry::make('file')
                    ->alignCenter()
                    ->view('filament.infolists.entries.video-player')
                    ->hiddenLabel()
                    ->visible(fn ($record) => Support\Str::contains($record->file, ['.mp4'])),
            ])
            ->columns(1);
    }

    protected function role(): int {
        return auth()->guard()->user()->role;
    }
}
