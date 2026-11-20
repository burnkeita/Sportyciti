<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Comments\CommentResource;
use App\Models\Comment;
use App\Support\Permissions;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentComments extends TableWidget
{
    protected static ?int $sort = 5;

    protected static ?string $heading = 'Recent comments';

    public static function canView(): bool
    {
        return auth()->user()?->can(Permissions::MODERATE_COMMENTS) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Comment::query()->with(['user:id,name', 'article:id,title'])->latest()->limit(6))
            ->paginated(false)
            ->headerActions([Action::make('moderate')->label('Moderation queue')->url(CommentResource::getUrl())])
            ->columns([
                TextColumn::make('body')->limit(60)->wrap(),
                TextColumn::make('user.name')->label('Reader'),
                TextColumn::make('article.title')->limit(30),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->since(),
            ]);
    }
}
