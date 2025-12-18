<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Absence;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget;

class WidgetAbsenceChart extends ChartWidget
{
    protected ?string $heading = 'Widget Absence Chart';


    protected function getData(): array
    {
        $color = '#fbbf24'; // warna kuning Tailwind warning
        $colorTransparent = 'rgba(251, 191, 36, 0.3)';
        $data = Trend::model(Absence::class)
            ->between(
                start: now()->subMonths(6),
                end: now(),
            )
            ->perMonth()
            ->count();
        return [
            'datasets' => [
                [
                    'label' => 'Absences',
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
