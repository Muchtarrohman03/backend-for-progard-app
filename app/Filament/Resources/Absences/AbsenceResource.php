<?php

namespace App\Filament\Resources\Absences;

use UnitEnum;
use BackedEnum;
use App\Models\Absence;
use Filament\Tables\Table;
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
use App\Filament\Exports\AbsenceExporter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use App\Filament\Resources\Absences\Pages\ManageAbsences;

class AbsenceResource extends Resource
{
    protected static ?string $model = Absence::class;

    protected static string | UnitEnum | null $navigationGroup = 'Manajemen Pengajuan';

    protected static ?string $navigationLabel = 'Pengajuan Izin';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Select::make('reason')
                //     ->options(['sakit' => 'Sakit', 'darurat' => 'Darurat', 'lainnya' => 'Lainnya'])
                //     ->required(),
                // DatePicker::make('start')
                //     ->required(),
                // DatePicker::make('end')
                //     ->required(),
                // Textarea::make('description')
                //     ->columnSpanFull(),
                // // Relasi ke User (employee)
                // Select::make('employee_id')
                //     ->label('Employee')
                //     ->relationship('employee', 'name') // relasi ke model User
                //     ->searchable()
                //     ->required(),
                // FileUpload::make('evidence')
                //     ->label('Evidence')
                //     ->maxSize(2048)
                //     ->disk('public')
                //     ->directory('absences/evidence')
                //     ->image()
                //     ->maxsize(2048),
                Select::make('status')
                    ->options(['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'])
                    ->default('pending')
                    ->required(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('reason')
                    ->badge(),
                TextEntry::make('start')
                    ->date(),
                TextEntry::make('end')
                    ->date(),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('employee_id')
                    ->numeric(),
                ImageEntry::make('image_url')
                    ->disk('public')
                    ->imageWidth(200)
                    ->imageHeight(300)
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reason')
                    ->label('Alasan')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('start')
                    ->label('Mulai')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('end')
                    ->label('Selesai')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('employee.name')
                    ->label('Karyawan')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('description')
                    ->label('Keterangan')
                    ->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('image_url')
                    ->label('Bukti')
                    ->disk('public')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ])
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->exporter(AbsenceExporter::class)
                    ->color('primary')
                    ->maxRows(50000)
                    ->chunkSize(1000),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAbsences::route('/'),
        ];
    }
}
