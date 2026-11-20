<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\URL;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with(['author:id,name', 'category:id,name', 'subcategory:id,name']))
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(70)
                    ->wrap()
                    ->description(fn (Article $record) => collect([$record->author?->name, $record->subcategory?->name ?? $record->category?->name])->filter()->implode(' · ')),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('type')->badge()->color('gray')->toggleable(),
                IconColumn::make('is_featured')->label('Featured')->boolean()->toggleable(),
                IconColumn::make('is_breaking')->label('Breaking')->boolean()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('published_at')->label('Published')->dateTime('j M Y, H:i')->sortable()->placeholder('—'),
                TextColumn::make('view_count')->label('Views')->numeric()->sortable()->toggleable(),
                TextColumn::make('updated_at')->label('Updated')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(ArticleStatus::class)->multiple(),
                SelectFilter::make('type')->options(ArticleType::class),
                SelectFilter::make('category_id')->label('Category')->options(fn () => Category::whereNull('parent_id')->pluck('name', 'id')),
                SelectFilter::make('author_id')->label('Author')->options(fn () => User::whereHas('articles')->orderBy('name')->pluck('name', 'id'))->searchable(),
                TernaryFilter::make('is_featured')->label('Featured'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (Article $record) => $record->isPublished() ? $record->url() : URL::temporarySignedRoute('articles.preview', now()->addHour(), $record))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
