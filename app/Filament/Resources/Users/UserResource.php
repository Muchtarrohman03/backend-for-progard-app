<?php

namespace App\Filament\Resources\Users;

use UnitEnum;
use BackedEnum;
use App\Models\User;
use App\Models\UserProfile;
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
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\Users\Pages\ManageUsers;
use Filament\Schemas\Components\Section;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $title = 'Kelola Pengguna';

    protected static string | UnitEnum | null $navigationGroup = 'Manajemen Pengguna';
    protected static ?string $navigationLabel = 'Kelola Pengguna';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Kredensial Akun')
                    ->schema([

                        TextInput::make('email')
                            ->email()
                            ->required(),

                        // TextInput::make('telephone')
                        //     ->tel(),

                        TextInput::make('password')
                            ->password()
                            ->default('123456')
                            ->required(fn(string $operation) => $operation === 'create')
                            ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                            ->dehydrated(fn($state) => filled($state)),
                    ]),

                Section::make('Profil Pengguna')
                    ->relationship('profile')
                    ->schema([

                        TextInput::make('name')
                            ->label('Nama Pengguna')
                            ->required(),

                        Select::make('division_id')
                            ->relationship('division', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('gender')
                            ->label('Jenis Kelamin')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                            ])
                            ->required(),
                    ]),

                Section::make('Peran')
                    ->schema([

                        Select::make('peran')
                            ->label('Peran Pengguna')
                            ->relationship('roles', 'name')
                            ->preload()
                            ->required(),
                    ]),
            ]);
    }
    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextEntry::make('profile.name')
                    ->label('Nama'),

                TextEntry::make('email')
                    ->label('Email'),
                TextEntry::make('roles.name')
                    ->label('Peran'),

                TextEntry::make('profile.division.name')
                    ->label('Divisi'),

                TextEntry::make('profile.gender')
                    ->label('Gender'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('id')
                    ->label('ID'),

                TextColumn::make('profile.name')
                    ->label('Nama')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Alamat Email')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->sortable()
                    ->wrap(),

                TextColumn::make('profile.division.name')
                    ->label('Divisi')
                    ->sortable(),

                TextColumn::make('profile.gender')
                    ->label('Gender')
                    ->sortable(),
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
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }
}
