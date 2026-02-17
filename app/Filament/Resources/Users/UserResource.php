<?php

namespace App\Filament\Resources\Users;

use Dom\Text;
use UnitEnum;
use BackedEnum;
use App\Models\User;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\Users\Pages\ManageUsers;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | UnitEnum | null $navigationGroup = 'Manajemen Pengguna';
    protected static ?string $navigationLabel = 'Kelola Pengguna';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('name')
                    ->required(),

                TextInput::make('email')
                    ->email()
                    ->autocomplete('off')
                    ->required(),

                TextInput::make('password')
                    ->password()
                    ->autocomplete('new-password')
                    ->required()
                    ->dehydrateStateUsing(fn($state) => bcrypt($state))
                    ->visible(fn(string $operation) => $operation === 'create'),

                // ⬇️ Tambahkan field assign role
                Select::make('roles')
                    ->label('Assign Roles')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload()
                    ->required(),
                Select::make('division')
                    ->options([
                        'Management' => 'Management',
                        'Sektor 1' => 'Sektor 1',
                        'Sektor 2' => 'Sektor 2',
                        'Sektor 3' => 'Sektor 3',
                        'Sektor 4' => 'Sektor 4',
                        'Sektor 5' => 'Sektor 5',
                    ])
                    ->required(),
                Select::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ])
                    ->required(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('roles.name')
                    ->label('Peran'),
                TextEntry::make('division')
                    ->label('Divisi')
                    ->label('Division'),
                TextEntry::make('gender')
                    ->label('Jenis Kelamin')
                    ->label('Gender'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Alamat Email')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label('Peran')
                    ->label('Roles')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
                TextColumn::make('division')
                    ->label('Divisi')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('gender')
                    ->label('Jenis Kelamin')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }
}
