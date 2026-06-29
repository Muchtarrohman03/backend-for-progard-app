<?php

namespace App\Filament\Exports;

use App\Models\Absence;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class AbsenceExporter extends Exporter
{
    protected static ?string $model = Absence::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('reason')
                ->label('Alasan'),
            ExportColumn::make('start')
                ->label('Tanggal Mulai'),
            ExportColumn::make('end')
                ->label('Tanggal Selesai'),
            ExportColumn::make('description')
                ->label('Deskripsi'),
            ExportColumn::make('employee.name')
                ->label('Nama Karyawan'),
            ExportColumn::make('evidence')
                ->label('Bukti'),
            ExportColumn::make('status')
                ->label('Status'),
            ExportColumn::make('image_url')
                ->label('URL Gambar'),
            ExportColumn::make('created_at')
                ->label('Dibuat'),
            ExportColumn::make('approver.name')
                ->label('Disetujui oleh'),
            ExportColumn::make('comment')
                ->label('Komentar'),
            ExportColumn::make('updated_at')
                ->label('Diperbarui'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $successCount = Number::format($export->successful_rows);
        $rowWord = str('baris')->plural($export->successful_rows);

        $body = "Export izin selesai. {$successCount} {$rowWord} berhasil diekspor.";

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $failedCount = Number::format($failedRowsCount);
            $failedWord = str('baris')->plural($failedRowsCount);
            $body .= " {$failedCount} {$rowWord} gagal diekspor, silakan coba lagi.";
        }

        return $body;
    }
}
