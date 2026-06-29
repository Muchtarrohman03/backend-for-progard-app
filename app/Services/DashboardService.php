<?php

namespace App\Services;

use App\Models\Absence;
use App\Models\JobSubmission;
use App\Models\Overtime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    private function buildRankingQuery()
    {
        return User::query()
            ->withCount('jobSubmissions')
            ->having('job_submissions_count', '>', 0)
            ->with('profile');
    }
    public function statOverview()
    {
        $user = Auth::user();

        return [
            'job_submissions' => $this->buildOverview(
                JobSubmission::where('employee_id', $user->id)
            ),

            'absences' => $this->buildOverview(
                Absence::where('employee_id', $user->id)
            ),

            'overtime' => $this->buildOverview(
                Overtime::where('employee_id', $user->id)
            ),
        ];
    }
    public function divisionOverview()
    {
        $divisionId = Auth::user()->division_id;

        return [
            'job_submissions' => $this->buildOverview(
                JobSubmission::whereHas('employee.profile', function ($q) use ($divisionId) {
                    $q->where('division_id', $divisionId);
                })
            ),

            'absences' => $this->buildOverview(
                Absence::whereHas('employee.profile', function ($q) use ($divisionId) {
                    $q->where('division_id', $divisionId);
                })
            ),

            'overtime' => $this->buildOverview(
                Overtime::whereHas('employee.profile', function ($q) use ($divisionId) {
                    $q->where('division_id', $divisionId);
                })
            ),
        ];
    }
    public function allOverview()
    {
        return [
            'job_submissions' => $this->buildOverview(
                JobSubmission::query()
            ),

            'absences' => $this->buildOverview(
                Absence::query()
            ),

            'overtime' => $this->buildOverview(
                Overtime::query()
            ),
        ];
    }

    private function buildOverview($query)
    {
        $stats = $query
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total' => $stats->sum(),
            'pending' => $stats['pending'] ?? 0,
            'approved' => $stats['approved'] ?? 0,
            'rejected' => $stats['rejected'] ?? 0,
        ];
    }

    public function weeklySubmissionChart()
    {
        $user = Auth::user();

        $today = Carbon::today();
        $startDate = $today->copy()->subDays(6);

        $submissions = JobSubmission::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total')
        )
            ->where('employee_id', $user->id)
            ->whereBetween('created_at', [
                $startDate->startOfDay(),
                $today->endOfDay()
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $result = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i)->toDateString();

            $result[] = [
                'date' => $date,
                'total' => $submissions[$date]->total ?? 0,
            ];
        }

        return $result;
    }

    public function gardenerRanking()
    {
        $supervisor = Auth::user();

        return $this->buildRankingQuery()

            ->whereHas('profile', function ($q) use ($supervisor) {

                $q->where(
                    'division_id',
                    $supervisor->division_id
                );
            })

            ->role('gardener')

            ->orderByDesc('job_submissions_count')

            ->get()

            ->map(fn($user) => [

                'label' => $user->name,

                'value' => $user->job_submissions_count,
            ]);
    }
    public function allRanking()
    {
        return User::query()

            ->withCount('jobSubmissions')

            ->whereHas('jobSubmissions')

            ->with('profile.division')

            ->orderByDesc('job_submissions_count')

            ->get()

            ->map(fn($user) => [

                'label' => $user->name,

                'value' => $user->job_submissions_count,

                'division' => $user->profile?->division?->name,

                'role' => $user->getRoleNames()->first(),
            ]);
    }
}
