<?php

namespace App\Filament\Resources\Roles;

use App\Support\Permissions;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;
use UnitEnum;

/** Role and permission management — restricted to holders of "manage roles" (Super Administrators). */
class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Roles & permissions';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can(Permissions::MANAGE_ROLES) ?? false;
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canViewAny() && $record->name !== Permissions::SUPER_ADMIN;
    }

    public static function canDelete(Model $record): bool
    {
        return static::canViewAny() && ! in_array($record->name, Permissions::staffRoles(), true);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(60)->unique(ignoreRecord: true),
            CheckboxList::make('permissions')->relationship('permissions', 'name')->columns(2)->bulkToggleable()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount(['permissions', 'users']))
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('permissions_count')->label('Permissions')->formatStateUsing(fn ($state, Role $record) => $record->name === Permissions::SUPER_ADMIN ? 'All (bypass)' : $state),
                TextColumn::make('users_count')->label('Users'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageRoles::route('/')];
    }
}
