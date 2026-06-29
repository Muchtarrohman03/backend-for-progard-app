<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Overtime;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget;

class WidgetOvertimeChart extends ChartWidget
{
    protected ?string $heading = 'Grafik Laporan Lembur';
    public ?string $filter = 'year';


    // $color = 'rgba(75, 192, 192, 1)';
    // $colorTransparent  = 'rgba(75, 192, 192, 0.3)';

    protected function getData(): array
    {
        $filter = $this->filter ?? 'year';

        $trend = Trend::model(Overtime::class);

        switch ($filter) {
            case '7days':
                $data = $trend
                    ->between(
                        start: now()->subDays(7),
                        end: now(),
                    )
                    ->perDay()
                    ->count();

                $labels = $data->map(
                    fn(TrendValue $value) =>
                    Carbon::parse($value->date)
                        ->locale('id')
                        ->translatedFormat('d M')
                );

                break;

            case '30days':
                $data = $trend
                    ->between(
                        start: now()->subDays(30),
                        end: now(),
                    )
                    ->perDay()
                    ->count();

                $labels = $data->map(
                    fn(TrendValue $value) =>
                    Carbon::parse($value->date)
                        ->locale('id')
                        ->translatedFormat('d M')
                );

                break;

            case '90days':
                $data = $trend
                    ->between(
                        start: now()->subDays(90),
                        end: now(),
                    )
                    ->perDay()
                    ->count();

                $labels = $data->map(
                    fn(TrendValue $value) =>
                    Carbon::parse($value->date)
                        ->locale('id')
                        ->translatedFormat('d M')
                );

                break;

            case 'year':
                $data = $trend
                    ->between(
                        start: now()->startOfYear(),
                        end: now(),
                    )
                    ->perMonth()
                    ->count();

                $labels = $data->map(
                    fn(TrendValue $value) =>
                    Carbon::parse($value->date)
                        ->locale('id')
                        ->translatedFormat('F')
                );

                break;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Laporan Lembur',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                    //ganti warna kuning
                    'borderColor' => '#facc15',
                    'backgroundColor' => 'rgba(250, 204, 21, 0.2)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels->toArray(),
        ];
    }
    protected function getFilters(): ?array
    {
        return [
            '7days' => '7 Hari Terakhir',
            '30days' => '30 Hari Terakhir',
            '90days' => '90 Hari Terakhir',
            'year' => 'Tahun Ini',
        ];
    }
    protected function getType(): string
    {
        return 'line';
    }
}
