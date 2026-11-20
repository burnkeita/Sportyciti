<?php

namespace App\Filament\Resources\Comments;

use App\Enums\CommentStatus;
use App\Models\Comment;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use UnitEnum;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|UnitEnum|null $navigationGroup = 'Newsroom';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Comment moderation';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        $moderate = fn (CommentStatus $status) => fn (Comment $record) => $record->moderate($status, auth()->user());

        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with(['user:id,name,email', 'article:id,title,slug']))
            ->columns([
                TextColumn::make('body')->label('Comment')->wrap()->limit(220)->searchable(),
                TextColumn::make('user.name')->label('Reader')->description(fn (Comment $record) => $record->user?->email)->searchable(),
                TextColumn::make('article.title')->label('Article')->limit(40)->url(fn (Comment $record) => $record->article?->url(), shouldOpenInNewTab: true),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(CommentStatus::class)->default(CommentStatus::Pending->value),
            ])
            ->recordActions([
                Action::make('approve')->icon('heroicon-o-check')->color('success')
                    ->visible(fn (Comment $record) => $record->status !== CommentStatus::Approved)
                    ->action($moderate(CommentStatus::Approved)),
                Action::make('reject')->icon('heroicon-o-x-mark')->color('gray')
                    ->visible(fn (Comment $record) => $record->status !== CommentStatus::Rejected)
                    ->action($moderate(CommentStatus::Rejected)),
                Action::make('spam')->icon('heroicon-o-shield-exclamation')->color('danger')
                    ->visible(fn (Comment $record) => $record->status !== CommentStatus::Spam)
                    ->action($moderate(CommentStatus::Spam)),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approveSelected')->label('Approve selected')->icon('heroicon-o-check')
                        ->action(fn (Collection $records) => $records->each->moderate(CommentStatus::Approved, auth()->user()))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('spamSelected')->label('Mark as spam')->icon('heroicon-o-shield-exclamation')->color('danger')
                        ->action(fn (Collection $records) => $records->each->moderate(CommentStatus::Spam, auth()->user()))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = Comment::where('status', CommentStatus::Pending)->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageComments::route('/')];
    }
}
