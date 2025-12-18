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

class OvertimeResource extends Resource
{
    protected static ?string $model = Overtime::class;

    protected static string | UnitEnum | null $navigationGroup = 'Submission Management';

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
                    ->time(),
                TextEntry::make('end')
                    ->time(),
                TextEntry::make('category_id')
                    ->numeric(),
                TextEntry::make('employee_id')
                    ->numeric(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('submitted_at')
                    ->dateTime(),
                ImageEntry::make('image_url')
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
                TextColumn::make('start')
                    ->time()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('end')
                    ->time()
                    ->searchable()
                    ->sortable(),
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
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
                ImageColumn::make('image_url')
                    ->label('Image')
                    ->disk('public'),
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
                    ->exporter(OvertimeExporter::class)
                    ->label('Export Overtimes')
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
