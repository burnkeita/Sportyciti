<?php

namespace App\Filament\Resources\NewsletterSubscribers;

use App\Models\NewsletterSubscriber;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static string|UnitEnum|null $navigationGroup = 'Engagement';

    protected static ?string $navigationLabel = 'Newsletter subscribers';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Action::make('export')
                    ->label('Export confirmed (CSV)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (): StreamedResponse => response()->streamDownload(function () {
                        $out = fopen('php://output', 'w');
                        fputcsv($out, ['email', 'name', 'confirmed_at', 'source']);
                        NewsletterSubscriber::where('status', 'confirmed')->orderBy('id')->chunk(500, function ($rows) use ($out) {
                            foreach ($rows as $row) {
                                fputcsv($out, [$row->email, $row->name, $row->confirmed_at?->toDateTimeString(), $row->source]);
                            }
                        });
                        fclose($out);
                    }, 'sportyciti-newsletter-'.now()->format('Y-m-d').'.csv')),
            ])
            ->columns([
                TextColumn::make('email')->searchable()->copyable(),
                TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'confirmed' => 'success', 'pending' => 'warning', default => 'gray'
                }),
                TextColumn::make('source')->placeholder('—'),
                TextColumn::make('confirmed_at')->dateTime('j M Y')->placeholder('—')->sortable(),
                TextColumn::make('created_at')->label('Signed up')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(['pending' => 'Pending', 'confirmed' => 'Confirmed', 'unsubscribed' => 'Unsubscribed']),
            ])
            ->recordActions([DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageNewsletterSubscribers::route('/')];
    }
}
