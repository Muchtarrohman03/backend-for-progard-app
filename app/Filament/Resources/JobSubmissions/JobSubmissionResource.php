<?php

namespace App\Filament\Resources\JobSubmissions;

use UnitEnum;
use BackedEnum;
use Filament\Tables\Table;
use App\Models\JobCategory;
use Filament\Schemas\Schema;
use App\Models\JobSubmission;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ExportAction;
use Filament\Tables\Filters\Filter;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Exports\Models\Export;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\DateTimePicker;
use Filament\Infolists\Components\ImageEntry;
use App\Filament\Exports\JobSubmissionExporter;
use App\Filament\Resources\JobSubmissions\Pages\ManageJobSubmissions;

class JobSubmissionResource extends Resource
{
    protected static ?string $model = JobSubmission::class;

    protected static string | UnitEnum | null $navigationGroup = 'Manajemen Pengajuan';

    protected static ?string $navigationLabel = 'Laporan Pekerjaan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            // Relasi ke JobCategory
            // Select::make('category_id')
            //     ->label('Job Category')
            //     ->options(fn() => JobCategory::orderBy('name')->pluck('name', 'id')->toArray())
            //     ->searchable()
            //     ->required(),

            // // Relasi ke User (employee)
            // Select::make('employee_id')
            //     ->label('Employee')
            //     ->relationship('employee', 'name') // relasi ke model User
            //     ->searchable()
            //     ->required(),

            // // Waktu pengajuan
            // DateTimePicker::make('submitted_at')
            //     ->label('Submitted At')
            //     ->required(),

            // Status pekerjaan
            Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ])
                ->default('pending')
                ->required(),

            // // Upload gambar bukti/laporan
            // FileUpload::make('image_path')
            //     ->disk('public')
            //     ->label('Submission Image')
            //     ->directory('job_submissions') // simpan di folder storage/app/public/job_submissions
            //     ->image()
            //     ->maxSize(2048), // batas 2MB
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('category.name')
                    ->label('Kategori Pekerjaan'),
                TextEntry::make('employee.name')
                    ->label('Karyawan'),
                TextEntry::make('submitted_at')
                    ->label('Dibuat pada')
                    ->dateTime(),
                TextEntry::make('status')
                    ->badge(),
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // ID Laporan Pekerjaan
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
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

                TextColumn::make('submitted_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),
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
                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->multiple(),

                Filter::make('submitted_at')
                    ->label('Tanggal Submit')
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
                                $query->whereDate('submitted_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date) =>
                                $query->whereDate('submitted_at', '<=', $date),
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
                    ->icon(Heroicon::ArrowDownTray)
                    ->label('Unduh')
                    ->exporter(JobSubmissionExporter::class)
                    ->color('primary')
                    // opsional: limit baris, disk, format
                    ->maxRows(50000)
                    ->chunkSize(1000),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageJobSubmissions::route('/'),
        ];
    }
}
