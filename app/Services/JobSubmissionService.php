<?php

namespace App\Services;

use App\Models\JobSubmission;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class JobSubmissionService
{
    public function getAll()
    {
        return JobSubmission::query()
            ->with(['employee:id,name,division', 'category:id,name'])
            ->latest('submitted_at')
            ->get();
    }

    public function getByDate(string $date)
    {
        return JobSubmission::query()
            ->with(['employee:id,name,division', 'category:id,name'])
            ->where('employee_id', Auth::id())
            ->whereDate('submitted_at', $date)
            ->latest('submitted_at')
            ->get();
    }
    public function getByDivisionAndDate(string $division, string $date)
    {
        return JobSubmission::with(['employee', 'category'])
            ->whereRelation('employee', 'division', $division)
            ->whereDate('submitted_at', $date)
            ->latest('submitted_at')
            ->get();
    }

    public function store(array $data): JobSubmission
    {
        return JobSubmission::create([
            'category_id'  => $data['category_id'],
            'employee_id'  => Auth::id(),
            'submitted_at' => now(),
            'status'       => $data['status'] ?? 'pending',
            'before'       => $data['before']->store('job_submissions/before', 'public'),
            'after'        => $data['after']->store('job_submissions/after', 'public'),
        ]);
    }

    public function approve(JobSubmission $submission, string $status): JobSubmission
    {
        if ($submission->status !== 'pending') {
            throw new \Exception('Submission already processed');
        }

        $submission->update([
            'status' => $status,
            'approved_by' => Auth::id(),
        ]);

        return $submission->fresh();
    }

    public function getTodaySubmissionsByDivision(): array
    {
        $user = Auth::user();

        $submissions = JobSubmission::query()
            ->select([
                'id',
                'employee_id',
                'category_id',
                'status',
                'before',
                'after',
                'submitted_at',
                'created_at'
            ])
            ->with([
                'employee:id,name,division',
                'category:id,name',
            ])
            ->whereDate('submitted_at', today())
            ->whereHas(
                'employee',
                fn($q) =>
                $q->where('division', $user->division)
                    ->role('gardener')
            )
            ->latest('submitted_at')
            ->get();

        return [
            'data' => $submissions,
            'message' => $submissions->isEmpty()
                ? 'Data Laporan Kerja Hari Ini Tidak Ditemukan'
                : 'Data Laporan Kerja Hari Ini Ditemukan'
        ];
    }

    public function siteManagerSelectToday()
    {
        return JobSubmission::with(['employee:id,name,division', 'category:id,name'])
            ->whereDate('submitted_at', today())
            ->whereHas(
                'employee',
                fn($q) =>
                $q->role(['supervisor', 'staff'])
            )
            ->latest('submitted_at')
            ->get();
    }
    public function siteManagerApproveSupervisorAndStaffSubmission(int $id, string $status)
    {

        $submissions = JobSubmission::with('employee')
            ->findOrFail($id);

        //jika role employee bukan supervisor maka tidak bisa approve
        if (!$submissions->employee->hasAnyRole(['supervisor', 'staff'])) {
            throw new HttpException(
                403,
                'Only supervisor and staff job submission can be approved'
            );
        }

        $submissions->update([
            'status' => $status,
            'approved_by' => Auth::id(),
        ]);

        return $submissions->load(['employee', 'category']);
    }


    public function summary(): array
    {
        $userId = Auth::id();

        return [
            'todayCount' => JobSubmission::query()
                ->where('employee_id', $userId)
                ->whereDate('submitted_at', today())
                ->count(),

            'yesterdayCount' => JobSubmission::query()
                ->where('employee_id', $userId)
                ->whereDate('submitted_at', today()->subDay())
                ->count(),
        ];
    }
    public function getWeeklySubmissionSummaryForCurrentUser(): array
    {
        $user = Auth::user();

        $today = Carbon::today();
        $startDate = $today->copy()->subDays(6);

        $submissions = JobSubmission::select(
            DB::raw('DATE(submitted_at) as date'),
            DB::raw('COUNT(*) as total')
        )
            ->where('employee_id', $user->id) // 🔥 filter by logged-in user
            ->whereBetween('submitted_at', [
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
    public function destroy(JobSubmission $submission): void
    {
        if ($submission->status !== 'pending') {
            throw new \Exception('Only pending submissions can be deleted');
        }

        $submission->delete();
    }
}
