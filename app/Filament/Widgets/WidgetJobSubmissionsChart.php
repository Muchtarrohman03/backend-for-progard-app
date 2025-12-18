<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget;
use App\Models\JobSubmission; // ✅ kapitalisasi diperbaiki

class WidgetJobSubmissionsChart extends ChartWidget
{
    protected ?string $heading = 'Grafik Pengajuan Pekerjaan';

    protected function getData(): array
    {
        $color = '#22c55e'; // success (green-500)
        $colorTransparent = 'rgba(34, 197, 94, 0.3)';

        $data = Trend::model(JobSubmission::class)
            ->between(
                start: now()->subMonths(6),
                end: now(),
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Job Submissions',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                    'borderColor' => $color,
                    'backgroundColor' => $colorTransparent,
                    'pointBackgroundColor' => $color,
                    'pointBorderColor' => $color,
                    'pointHoverBackgroundColor' => $color,
                    'pointHoverBorderColor' => $color,
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
            'labels' => $data->map(function (TrendValue $value) {
                // parse supaya jadi Carbon object → aman memanggil locale()/translatedFormat()
                $date = Carbon::parse($value->date)->locale('id');

                // 'F Y' = Bulan penuh + Tahun (contoh: "November 2025")
                return $date->translatedFormat('F Y');
            })->values()->toArray(),
        ];
    }
    protected function getFilters(): ?array
    {
        return [
            'today' => 'Hari ini',
            'week' => 'Minggu ini',
            'month' => 'Bulan ini',
            'year' => 'Tahun ini',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
