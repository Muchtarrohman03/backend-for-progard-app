<?php

namespace App\Filament\Resources\Overtimes;

use App\Filament\Exports\OvertimeExporter;
use App\Filament\Resources\Overtimes\Pages\ManageOvertimes;
use App\Models\JobCategory;
use App\Models\Overtime;
use App\Models\User;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use PhpParser\Node\Stmt\Label;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class OvertimeResource extends Resource
{
    protected static ?string $model = Overtime::class;



    public function getTitle(): string | Htmlable
    {
        return __('Custom Page Title');
    }

    protected static string | UnitEnum | null $navigationGroup = 'Manajemen Pengajuan';

    protected static ?string $navigationLabel = 'Laporan Lembur';

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
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('start')
                    ->label('Mulai')
                    ->time(),
                TextEntry::make('end')
                    ->label('Selesai')
                    ->time(),
                TextEntry::make('category.name')
                    ->label('Kategori'),
                TextEntry::make('employee.profile.name')
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
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
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
                TextColumn::make('employee.profile.name')
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
                TextColumn::make('created_at')
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
                TextColumn::make('approver.profile.name')
                    ->label('Penyetuju')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
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
