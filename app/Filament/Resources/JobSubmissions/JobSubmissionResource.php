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
use Filament\Actions\DeleteAction;
use Filament\Actions\ExportAction;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Exports\Models\Export;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\DateTimePicker;
use Filament\Infolists\Components\ImageEntry;
use App\Filament\Exports\JobSubmissionExporter;
use App\Filament\Resources\JobSubmissions\Pages\ManageJobSubmissions;

class JobSubmissionResource extends Resource
{
    protected static ?string $model = JobSubmission::class;

    protected static string | UnitEnum | null $navigationGroup = 'Submission Management';

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
                TextEntry::make('category_id')
                    ->numeric(),
                TextEntry::make('employee_id')
                    ->numeric(),
                TextEntry::make('submitted_at')
                    ->dateTime(),
                TextEntry::make('status')
                    ->badge(),
                ImageEntry::make('image_url')
                    ->label('Image')
                    ->disk('public')
                    ->imageWidth(200)
                    ->imageHeight(300)
                    ->placeholder('-'),
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
                // Nama kategori dari relasi
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),

                // Nama karyawan dari relasi
                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
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
                ImageColumn::make('image_url')
                    ->disk('public')
                    ->label('Image')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('status')
                    ->label('Filter by Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->multiple() // kalau ingin bisa pilih lebih dari satu
                    ->default(null),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                ExportAction::make()
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
