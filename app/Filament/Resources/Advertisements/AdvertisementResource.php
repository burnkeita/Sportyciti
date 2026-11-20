<?php

namespace App\Filament\Resources\Advertisements;

use App\Models\Advertisement;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class AdvertisementResource extends Resource
{
    protected static ?string $model = Advertisement::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|UnitEnum|null $navigationGroup = 'Advertising';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Campaigns';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->label('Campaign name')->required()->maxLength(120),
            TextInput::make('advertiser')->maxLength(120),
            Select::make('ad_placement_id')->label('Placement')->relationship('placement', 'name')->required()->preload(),
            Select::make('type')->options(['image' => 'Image banner', 'adsense' => 'Google AdSense unit', 'html' => 'Custom HTML / third-party tag'])->required()->default('image')->live(),
            Section::make('Creative')->columnSpanFull()->columns(2)->schema([
                FileUpload::make('image_path')->label('Desktop image')->image()->disk('public')->directory('ads')->visibility('public')->maxSize(2048)
                    ->visible(fn (Get $get) => $get('type') === 'image')->required(fn (Get $get) => $get('type') === 'image'),
                FileUpload::make('mobile_image_path')->label('Mobile image (optional)')->image()->disk('public')->directory('ads')->visibility('public')->maxSize(1024)
                    ->visible(fn (Get $get) => $get('type') === 'image'),
                TextInput::make('alt_text')->maxLength(191)->visible(fn (Get $get) => $get('type') === 'image'),
                TextInput::make('target_url')->label('Destination URL')->url()->maxLength(500)
                    ->visible(fn (Get $get) => $get('type') === 'image')->required(fn (Get $get) => $get('type') === 'image'),
                TextInput::make('adsense_slot')->label('AdSense slot ID')->maxLength(40)
                    ->helperText('Set the AdSense publisher ID in Site settings.')
                    ->visible(fn (Get $get) => $get('type') === 'adsense')->required(fn (Get $get) => $get('type') === 'adsense'),
                Textarea::make('html_code')->label('HTML / script tag')->rows(6)->columnSpanFull()
                    ->helperText('Only paste tags from trusted ad partners. This code runs on public pages.')
                    ->visible(fn (Get $get) => $get('type') === 'html')->required(fn (Get $get) => $get('type') === 'html'),
            ]),
            Section::make('Schedule & targeting')->columnSpanFull()->columns(4)->schema([
                DateTimePicker::make('starts_at')->seconds(false)->native(false),
                DateTimePicker::make('ends_at')->seconds(false)->native(false)->after('starts_at'),
                Select::make('device')->options(['all' => 'All devices', 'desktop' => 'Desktop only', 'mobile' => 'Mobile only'])->default('all'),
                TextInput::make('priority')->numeric()->default(0)->helperText('Higher wins; ties rotate.'),
                Toggle::make('is_active')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('placement'))
            ->columns([
                TextColumn::make('name')->searchable()->description(fn (Advertisement $record) => $record->advertiser),
                TextColumn::make('placement.name')->label('Placement'),
                TextColumn::make('type')->badge(),
                TextColumn::make('schedule')->state(fn (Advertisement $record) => ($record->starts_at?->format('j M') ?? 'Now').' → '.($record->ends_at?->format('j M Y') ?? 'open')),
                TextColumn::make('impressions')->numeric()->sortable(),
                TextColumn::make('clicks')->numeric()->sortable(),
                TextColumn::make('ctr')->label('CTR')->state(fn (Advertisement $record) => $record->clickThroughRate().'%'),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->filters([
                SelectFilter::make('ad_placement_id')->label('Placement')->relationship('placement', 'name'),
                SelectFilter::make('type')->options(['image' => 'Image', 'adsense' => 'AdSense', 'html' => 'HTML']),
            ])
            ->recordActions([EditAction::make(), ReplicateAction::make()->excludeAttributes(['impressions', 'clicks']), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageAdvertisements::route('/')];
    }
}
