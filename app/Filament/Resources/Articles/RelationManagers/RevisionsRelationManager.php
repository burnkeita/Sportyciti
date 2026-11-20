<?php

namespace App\Filament\Resources\Articles\RelationManagers;

use App\Models\ArticleRevision;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/** Read-only revision history with one-click restore of an earlier version. */
class RevisionsRelationManager extends RelationManager
{
    protected static string $relationship = 'revisions';

    protected static ?string $title = 'Revision history';

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return true;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('title'),
            TextEntry::make('excerpt'),
            TextEntry::make('body')->html()->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Saved')->dateTime('j M Y, H:i:s'),
                TextColumn::make('user.name')->label('By')->placeholder('System'),
                TextColumn::make('status')->label('Status at the time')->badge(),
                TextColumn::make('title')->limit(60),
                TextColumn::make('note')->placeholder('—'),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('restore')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->modalDescription('Replace the current headline, excerpt, body and insight components with this revision. The current version is kept in the history.')
                    ->visible(fn () => auth()->user()->can('update', $this->getOwnerRecord()))
                    ->action(function (ArticleRevision $record) {
                        $article = $this->getOwnerRecord();
                        $article->fill([
                            'title' => $record->title,
                            'excerpt' => $record->excerpt,
                            'body' => $record->body,
                            'insight_blocks' => $record->insight_blocks,
                        ])->save();

                        Notification::make()->title('Revision restored')->success()->send();
                        $this->redirect(request()->header('Referer') ?? url()->previous());
                    }),
            ]);
    }
}
