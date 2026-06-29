<?php

namespace App\Filament\Resources\Absences;

use App\Filament\Exports\AbsenceExporter;
use App\Filament\Resources\Absences\Pages\ManageAbsences;
use App\Models\Absence;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Table;
use UnitEnum;

class AbsenceResource extends Resource
{
    protected static ?string $model = Absence::class;

    protected static string | UnitEnum | null $navigationGroup = 'Manajemen Pengajuan';

    protected static ?string $navigationLabel = 'Laporan Izin';

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
                TextEntry::make('employee.profile.name')
                    ->label('Karyawan'),
                TextEntry::make('reason')
                    ->badge(),
                TextEntry::make('start')
                    ->date(),
                TextEntry::make('end')
                    ->date(),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                ImageEntry::make('image_url')
                    ->disk('public')
                    ->imageWidth(400)
                    ->imageHeight(300)
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge()
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'approved',
                        'heroicon-o-x-circle' => 'rejected',
                    ])
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                TextEntry::make('comment')
                    ->label('Komentar')
                    ->placeholder('-')
                    ->wrap(),
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
                TextColumn::make('employee.profile.name')
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
                TextColumn::make('comment')
                    ->label('Komentar')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->multiple(),

                Filter::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->form([
                        DatePicker::make('from')
                            ->label('Dari tanggal'),
                        DatePicker::make('until')
                            ->label('Sampai tanggal'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date) =>
                                $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date) =>
                                $query->whereDate('created_at', '<=', $date),
                            );
                    }),
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
                    ->icon(Heroicon::DocumentArrowUp)
                    ->label('Ekspor')
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
