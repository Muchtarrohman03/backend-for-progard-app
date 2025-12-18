<?php

namespace App\Filament\Widgets;

use App\Models\Absence;
use App\Models\User;
use App\Models\JobSubmission;
use App\Models\JobCategory;
use App\Models\Overtime;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Override;

class StatsOverview extends BaseWidget
{
    protected function getHeading(): ?string
    {
        return 'Overview'; // ini akan tampil di atas kumpulan stat
    }
    // gunakan tipe yang diterima: int|array|null
    // untuk menampilkan 5 kolom pada breakpoint besar gunakan array responsive:
    protected int|array|null $columns = [
        'default' => 2, // kolom default (small)
        'md' => 3,      // medium
        'xl' => 3,     // extra-large -> 5 kolom
        '2xl' => 5,
    ];
    protected function getStats(): array
    {
        return [
            // $this->uniformStat(Stat::make('Total Users', User::count())
            //     ->description('Total pengguna')
            //     ->descriptionIcon('heroicon-o-users')
            //     ->color('success'),),

            $this->uniformStat(Stat::make('Job Submissions', JobSubmission::count())
                ->description('Total pengajuan pekerjaan')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('success'),),

            // $this->uniformStat(Stat::make('Job Categories', JobCategory::count())
            //     ->description('Total Kategori pekerjaan ')
            //     ->descriptionIcon('heroicon-o-folder')
            //     ->color('danger'),),
            $this->uniformStat(Stat::make('Absences Request', Absence::count())
                ->description('Total pengajuan izin ')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('warning'),),
            $this->uniformStat(Stat::make('Overtime Request', Overtime::count())
                ->description('Total pengajuan lembur ')
                ->descriptionIcon('heroicon-o-clock')
                ->color('primary'),),
        ];
    }
    protected function uniformStat(Stat $stat): Stat
    {
        return $stat->extraAttributes([
            'class' => 'h-full flex flex-col justify-center items-center text-center truncate',
        ]);
    }
}
