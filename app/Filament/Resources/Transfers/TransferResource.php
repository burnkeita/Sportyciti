<?php

namespace App\Filament\Resources\Transfers;

use App\Enums\TransferStatus;
use App\Models\Player;
use App\Models\Team;
use App\Models\Transfer;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class TransferResource extends Resource
{
    protected static ?string $model = Transfer::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string|UnitEnum|null $navigationGroup = 'Football';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Transfers & rumours';

    protected static ?string $recordTitleAttribute = 'player_name';

    public static function form(Schema $schema): Schema
    {
        $isRumour = fn (Get $get) => in_array($get('status'), ['rumour', TransferStatus::Rumour], true);

        return $schema->columns(2)->components([
            Section::make('Move')->columnSpanFull()->columns(2)->schema([
                Select::make('player_id')->label('Player profile')->relationship('player', 'name')->searchable()
                    ->live()
                    ->afterStateUpdated(fn (Set $set, $state) => $state && $set('player_name', Player::find($state)?->name))
                    ->createOptionForm([TextInput::make('name')->required()]),
                TextInput::make('player_name')->required()->maxLength(120),
                Select::make('from_team_id')->label('From club')->relationship('fromTeam', 'name')->searchable()
                    ->live()->afterStateUpdated(fn (Set $set, $state) => $state && $set('from_team_name', Team::find($state)?->name)),
                TextInput::make('from_team_name')->label('From club (name)')->maxLength(120)->helperText('Used when the club is not in our database.'),
                Select::make('to_team_id')->label('To club')->relationship('toTeam', 'name')->searchable()
                    ->live()->afterStateUpdated(fn (Set $set, $state) => $state && $set('to_team_name', Team::find($state)?->name)),
                TextInput::make('to_team_name')->label('To club (name)')->maxLength(120),
            ]),
            Section::make('Status')->columnSpanFull()->columns(3)->schema([
                Select::make('status')->options(TransferStatus::class)->required()->live()
                    ->helperText('Only mark as confirmed once officially announced by a club or governing body.'),
                Select::make('reliability')->label('Source reliability')->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'])->visible($isRumour),
                TextInput::make('transfer_window')->placeholder('Summer 2026')->maxLength(40),
                DateTimePicker::make('reported_at')->required()->default(now())->seconds(false)->native(false),
                DateTimePicker::make('confirmed_at')->seconds(false)->native(false)->hidden($isRumour),
                TextInput::make('contract_until')->placeholder('June 2030')->maxLength(40),
            ]),
            Section::make('Fee')->columnSpanFull()->columns(4)->schema([
                TextInput::make('fee_amount')->numeric()->minValue(0),
                Select::make('fee_currency')->options(['EUR' => 'EUR', 'GBP' => 'GBP', 'USD' => 'USD', 'GHS' => 'GHS'])->default('EUR'),
                Toggle::make('fee_verified')->label('Fee verified')->helperText('Unverified fees are never displayed as figures.')->inline(false),
                TextInput::make('fee_note')->placeholder('Undisclosed')->maxLength(120),
            ]),
            Section::make('Source & story')->columnSpanFull()->columns(2)->schema([
                TextInput::make('source_name')->required()->maxLength(120)->placeholder('Club statement, GFA, journalist name…'),
                TextInput::make('source_url')->url()->maxLength(500),
                Select::make('article_id')->label('Related article')->relationship('article', 'title')->searchable(),
                Toggle::make('is_published')->label('Show on website')->default(true)->inline(false),
                Textarea::make('summary')->rows(3)->maxLength(1000)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('reported_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with(['fromTeam:id,name', 'toTeam:id,name']))
            ->columns([
                TextColumn::make('player_name')->label('Player')->searchable()->sortable(),
                TextColumn::make('move')->state(fn (Transfer $record) => ($record->fromName() ?? '?').' → '.($record->toName() ?? '?')),
                TextColumn::make('status')->badge(),
                TextColumn::make('fee')->state(fn (Transfer $record) => $record->feeLabel()),
                TextColumn::make('source_name')->label('Source')->limit(30),
                TextColumn::make('reported_at')->dateTime('j M Y')->sortable(),
                ToggleColumn::make('is_published')->label('Live'),
            ])
            ->filters([
                SelectFilter::make('status')->options(TransferStatus::class),
                SelectFilter::make('transfer_window')->options(fn () => Transfer::whereNotNull('transfer_window')->distinct()->pluck('transfer_window', 'transfer_window')),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageTransfers::route('/')];
    }
}
