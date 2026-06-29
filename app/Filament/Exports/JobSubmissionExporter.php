<?php

namespace App\Filament\Exports;

use App\Models\JobSubmission;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class JobSubmissionExporter extends Exporter
{
    protected static ?string $model = JobSubmission::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),

            ExportColumn::make('category.name')
                ->label('Kategori Pekerjaan'),

            ExportColumn::make('employee.name')   // ← ganti dari employee_id
                ->label('Karyawan'),

            ExportColumn::make('submitted_at')
                ->label('Dibuat pada'),

            ExportColumn::make('status')
                ->label('Status'),

            ExportColumn::make('before_url')
                ->label('Foto Sebelum'),

            ExportColumn::make('after_url')
                ->label('Foto Sesudah'),

            ExportColumn::make('approver.name')
                ->label('Disetujui oleh'),

            ExportColumn::make('comment')
                ->label('Komentar'),

            ExportColumn::make('created_at')
                ->label('Dibuat'),

            ExportColumn::make('updated_at')
                ->label('Diperbarui'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $successCount = Number::format($export->successful_rows);
        $rowWord = str('baris')->plural($export->successful_rows);

        $body = "Export laporan pekerjaan selesai. {$successCount} {$rowWord} berhasil diekspor.";

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $failedCount = Number::format($failedRowsCount);
            $failedWord = str('baris')->plural($failedRowsCount);
            $body .= " {$failedCount} {$rowWord} gagal diekspor, silakan coba lagi.";
        }

        return $body;
    }
}
