<?php

namespace App\Filament\Resources\Users;

use App\Models\User;
use App\Support\Permissions;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Password;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Users & authors';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        $isSuper = fn () => auth()->user()?->hasRole(Permissions::SUPER_ADMIN);

        return $schema->columns(2)->components([
            Section::make('Account')->columnSpanFull()->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(120),
                TextInput::make('email')->email()->required()->maxLength(191)->unique(ignoreRecord: true),
                TextInput::make('password')->password()->revealable()
                    ->rule(Password::defaults())
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->helperText('Leave blank to keep the current password.'),
                Select::make('roles')->relationship('roles', 'name', fn ($query) => $query->when(! $isSuper(), fn ($q) => $q->where('name', '!=', Permissions::SUPER_ADMIN)))
                    ->multiple()->preload()
                    ->helperText('No role = registered reader (no admin access).'),
                Toggle::make('is_active')->label('Active')->default(true)->helperText('Inactive staff cannot sign in to the admin panel.'),
            ]),
            Section::make('Public author profile')->columnSpanFull()->columns(2)->collapsible()->schema([
                Toggle::make('is_public_author')->label('Show public author page'),
                TextInput::make('slug')->label('Profile URL slug')->alphaDash()->unique(ignoreRecord: true)->maxLength(120),
                FileUpload::make('avatar_path')->label('Profile picture')->image()->avatar()->disk('public')->directory('avatars')->visibility('public')->maxSize(2048),
                Group::make()->schema([
                    TextInput::make('job_title')->maxLength(120),
                    TextInput::make('specialisation')->maxLength(120),
                ]),
                Textarea::make('bio')->rows(4)->maxLength(2000)->columnSpanFull(),
                KeyValue::make('social_links')->keyLabel('Network (x, facebook, instagram, linkedin, website)')->valueLabel('URL')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->with('roles'))
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->description(fn (User $record) => $record->email),
                TextColumn::make('roles.name')->badge()->placeholder('Reader'),
                IconColumn::make('is_public_author')->label('Author page')->boolean(),
                IconColumn::make('is_active')->label('Active')->boolean(),
                IconColumn::make('email_verified_at')->label('Verified')->boolean()->getStateUsing(fn (User $record) => $record->email_verified_at !== null),
                TextColumn::make('last_login_at')->since()->placeholder('never')->sortable(),
            ])
            ->filters([
                SelectFilter::make('roles')->relationship('roles', 'name')->multiple(),
                TernaryFilter::make('staff')->label('Staff only')
                    ->queries(true: fn ($q) => $q->whereHas('roles'), false: fn ($q) => $q->whereDoesntHave('roles')),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageUsers::route('/')];
    }
}
