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
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('employee.name')->label('Karyawan'),
            ExportColumn::make('category.name')->label('Kategori'),
            ExportColumn::make('start')->label('Waktu Mulai'),
            ExportColumn::make('end')->label('Waktu Selesai'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('submitted_at')->label('Dikirim Pada'),
            ExportColumn::make('before_url')->label('Foto Sebelum'),
            ExportColumn::make('after_url')->label('Foto Sesudah'),
            ExportColumn::make('approver.name')->label('Disetujui oleh'),
            ExportColumn::make('comment')->label('Komentar'),
            ExportColumn::make('created_at')->label('Dibuat'),
            ExportColumn::make('updated_at')->label('Diperbarui'),
        ];
    }
    public static function getCompletedNotificationBody(Export $export): string
    {
        $successCount = Number::format($export->successful_rows);
        $rowWord = str('baris')->plural($export->successful_rows);

        $body = "Export lembur selesai. {$successCount} {$rowWord} berhasil diekspor.";

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $failedCount = Number::format($failedRowsCount);
            $failedWord = str('baris')->plural($failedRowsCount);
            $body .= " {$failedCount} {$rowWord} gagal diekspor, silakan coba lagi.";
        }

        return $body;
    }
}
