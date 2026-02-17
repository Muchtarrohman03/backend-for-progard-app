<?php

namespace App\Filament\Resources\Overtimes;

use UnitEnum;
use BackedEnum;
use App\Models\User;
use App\Models\Overtime;
use Filament\Tables\Table;
use App\Models\JobCategory;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ExportAction;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TimePicker;
use App\Filament\Exports\OvertimeExporter;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\DateTimePicker;
use Filament\Infolists\Components\ImageEntry;
use App\Filament\Resources\Overtimes\Pages\ManageOvertimes;
use PhpParser\Node\Stmt\Label;

class OvertimeResource extends Resource
{
    protected static ?string $model = Overtime::class;

    protected static string | UnitEnum | null $navigationGroup = 'Manajemen Pengajuan';

    protected static ?string $navigationLabel = 'Pengajuan Lembur';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // TimePicker::make('start')
                //     ->required(),

                // TimePicker::make('end')
                //     ->required(),

                // Select::make('category_id')
                //     ->relationship('category', 'name')
                //     ->options(fn() => JobCategory::orderBy('name')->pluck('name', 'id')->toArray())
                //     ->searchable()
                //     ->required(),

                // Select::make('employee_id')
                //     ->relationship('employee', 'name')
                //     ->options(fn() => User::orderBy('name')->pluck('name', 'id')->toArray())
                //     ->searchable()
                //     ->required(),

                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->required(),

                // Textarea::make('description')
                //     ->columnSpanFull(),

                // DateTimePicker::make('submitted_at')
                //     ->required(),
                // FileUpload::make('image_path')
                //     ->label('Overtime Image')
                //     ->disk('public')
                //     ->directory('overtime')
                //     ->visibility('public')
                //     ->image()
                //     ->previewable(true)
                //     ->openable()
                //     ->downloadable()
                //     ->getUploadedFileNameForStorageUsing(
                //         fn($file) =>
                //         (string) str()->uuid() . '.' . $file->getClientOriginalExtension()
                //     )
                //     ->default(fn($record) => $record?->image_path
                //         ? asset('storage/' . $record->image_path)
                //         : null),

            ]);
    }


    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('start')
                    ->label('Mulai')
                    ->time(),
                TextEntry::make('end')
                    ->label('Selesai')
                    ->time(),
                TextEntry::make('category.name')
                    ->label('Kategori'),
                TextEntry::make('employee.name')
                    ->label('Karyawan'),

                TextEntry::make('status')
                    ->label('Status')
                    ->badge(),
                TextEntry::make('submitted_at')
                    ->label('Dibuat Pada')
                    ->dateTime(),
                ImageEntry::make('before_url')
                    ->label('Sebelum')
                    ->disk('public')
                    ->imageWidth(200)
                    ->imageHeight(300)
                    ->placeholder('-'),
                ImageEntry::make('after_url')
                    ->label('Sesudah')
                    ->disk('public')
                    ->imageWidth(200)
                    ->imageHeight(300)
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->label('Deskripsi')
                    ->placeholder('-')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('start')
                    ->label('Mulai')
                    ->time()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('end')
                    ->label('Selesai')
                    ->time()
                    ->searchable()
                    ->sortable(),
                // Nama kategori dari relasi
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable(),

                // Nama karyawan dari relasi
                TextColumn::make('employee.name')
                    ->label('Karyawan')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->icons(
                        [
                            'heroicon-o-clock' => 'pending',
                            'heroicon-o-check-circle' => 'approved',
                            'heroicon-o-x-circle' => 'rejected',
                        ]
                    )
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                TextColumn::make('submitted_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),
                ImageColumn::make('before_url')
                    ->disk('public')
                    ->label('Sebelum')
                    ->toggleable(isToggledHiddenByDefault: false),
                ImageColumn::make('after_url')
                    ->disk('public')
                    ->label('Sesudah')
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                //
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
            ])
            ->headerActions([
                ExportAction::make()
                    ->icon(Heroicon::ArrowDownTray)
                    ->label('Unduh')
                    ->exporter(OvertimeExporter::class)
                    ->color('primary')
                    ->chunkSize(100),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOvertimes::route('/'),
        ];
    }
    public static function canCreate(): bool
    {
        return false;
    }
}
