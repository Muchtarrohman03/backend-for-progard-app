<?php

namespace App\Filament\Exports;

use App\Models\Overtime;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class OvertimeExporter extends Exporter
{
    public static function getModel(): string
    {
        return Overtime::class;
    }

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id'),
            ExportColumn::make('employee.name')->label('Employee'),
            ExportColumn::make('category.name')->label('Category'),
            ExportColumn::make('start')->label('Start Time'),
            ExportColumn::make('end')->label('End Time'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('submitted_at')->label('Submitted At'),

            // Kembalikan ke text URL, BUKAN gambar
            ExportColumn::make('image_url')->label('Image URL'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your overtime export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
