<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class WidgetUserChart extends ChartWidget
{
    protected ?string $heading = 'Grafik Pertambahan Pengguna';
    public ?string $filter = 'year';


    // $color = 'rgba(75, 192, 192, 1)';
    // $colorTransparent  = 'rgba(75, 192, 192, 0.3)';

    protected function getData(): array
    {
        $filter = $this->filter ?? 'year';

        $trend = Trend::model(User::class);

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
                    'label' => 'Jumlah Pengguna',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                    //ganti warna merah
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
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
