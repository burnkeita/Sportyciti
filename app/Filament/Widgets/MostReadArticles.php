<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use App\Support\Permissions;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;

class MostReadArticles extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected static ?string $heading = 'Most read — last 7 days';

    public static function canView(): bool
    {
        return auth()->user()?->can(Permissions::VIEW_ANALYTICS) ?? false;
    }

    public function table(Table $table): Table
    {
        $since = now()->subDays(6)->toDateString();

        return $table
            ->query(fn () => Article::query()
                ->select('articles.*')
                ->selectSub(DB::table('article_views')->selectRaw('COALESCE(SUM(views),0)')->whereColumn('article_id', 'articles.id')->where('date', '>=', $since), 'recent_views')
                ->published()
                ->orderByDesc('recent_views'))
            ->paginated(false)
            ->defaultPaginationPageOption(8)
            ->columns([
                TextColumn::make('title')->limit(55)->url(fn (Article $record) => $record->url(), shouldOpenInNewTab: true),
                TextColumn::make('recent_views')->label('Views (7d)')->numeric(),
                TextColumn::make('view_count')->label('All time')->numeric(),
            ])
            ->modifyQueryUsing(fn ($query) => $query->limit(8));
    }
}
