<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Enums\ArticleStatus;
use App\Filament\Resources\Articles\ArticleResource;
use App\Models\Article;
use App\Services\Content\ArticleWorkflow;
use App\Support\Permissions;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\URL;

/**
 * @property Article $record
 */
class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return app(ArticleWorkflow::class)->normalise($data, auth()->user(), $this->record);
    }

    protected function getHeaderActions(): array
    {
        $workflow = app(ArticleWorkflow::class);
        $canPublish = fn () => auth()->user()->can(Permissions::PUBLISH_ARTICLES);

        return [
            Action::make('view')
                ->label(fn () => $this->record->isPublished() ? 'View live' : 'Preview')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn () => $this->record->isPublished() ? $this->record->url() : URL::temporarySignedRoute('articles.preview', now()->addHour(), $this->record))
                ->openUrlInNewTab(),

            Action::make('submitForReview')
                ->label('Submit for review')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn () => $this->record->status === ArticleStatus::Draft)
                ->requiresConfirmation()
                ->action(function () use ($workflow) {
                    $this->save(shouldRedirect: false);
                    $workflow->submitForReview($this->record);
                    Notification::make()->title('Submitted for editorial review')->success()->send();
                    $this->refreshFormData(['status']);
                }),

            Action::make('publish')
                ->label('Approve & publish')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () => $canPublish() && in_array($this->record->status, [ArticleStatus::Draft, ArticleStatus::InReview], true))
                ->requiresConfirmation()
                ->modalDescription('The article will go live immediately.')
                ->action(function () use ($workflow) {
                    $this->save(shouldRedirect: false);
                    $workflow->publishNow($this->record, auth()->user());
                    Notification::make()->title('Article published')->success()->send();
                    $this->refreshFormData(['status', 'published_at']);
                }),

            ActionGroup::make([
                Action::make('returnToAuthor')
                    ->label('Return to author')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->visible(fn () => $canPublish() && $this->record->status === ArticleStatus::InReview)
                    ->schema([Textarea::make('notes')->label('Notes for the author')->required()->rows(4)])
                    ->action(function (array $data) use ($workflow) {
                        $workflow->returnToDraft($this->record, auth()->user(), $data['notes']);
                        Notification::make()->title('Returned to author with notes')->success()->send();
                        $this->refreshFormData(['status', 'review_notes']);
                    }),
                Action::make('archive')
                    ->icon('heroicon-o-archive-box')
                    ->color('danger')
                    ->visible(fn () => $canPublish() && $this->record->status === ArticleStatus::Published)
                    ->requiresConfirmation()
                    ->modalDescription('The article will be removed from the public website but kept in the CMS.')
                    ->action(function () use ($workflow) {
                        $workflow->archive($this->record, auth()->user());
                        Notification::make()->title('Article archived')->success()->send();
                        $this->refreshFormData(['status']);
                    }),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ]),
        ];
    }
}
