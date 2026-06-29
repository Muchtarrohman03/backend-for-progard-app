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
            ->latest('created_at')
            ->get();
    }

    public function getByDate(string $date)
    {
        return JobSubmission::with(['employee', 'category'])
            ->where('employee_id', Auth::id())
            ->whereDate('created_at', $date)  // ← ganti ke created_at
            ->latest('created_at')
            ->get();
    }
    public function getByDivisionAndDate(string $division, string $date)
    {
        return JobSubmission::query()

            ->whereHas('employee.profile.division', function ($q) use ($division) {

                $q->where('name', $division);
            })

            ->whereDate('created_at', $date)

            ->latest('created_at')

            ->get();
    }

    public function store(array $data): JobSubmission
    {
        return JobSubmission::create([
            'category_id'  => $data['category_id'],
            'employee_id'  => Auth::id(),
            // 'submitted_at' => now(),
            'status'       => $data['status'] ?? 'pending',
            'before'       => $data['before']->store('job_submissions/before', 'public'),
            'after'        => $data['after']->store('job_submissions/after', 'public'),
        ]);
    }

    public function approve(JobSubmission $submission, string $status, string $comment = null): JobSubmission
    {
        if ($submission->status !== 'pending') {
            throw new \Exception('Submission already processed');
        }

        $submission->update([
            'status' => $status,
            'approved_by' => Auth::id(),
            'comment' => $comment,
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
                // 'submitted_at', ← hapus
                'created_at'
            ])
            ->with([
                'employee.profile.division',
                'category:id,name',
            ])
            ->whereDate('created_at', today())  // ← ganti submitted_at ke created_at

            ->whereHas('employee', function ($q) use ($user) {
                $q->role('gardener')
                    ->whereHas('profile', function ($profileQuery) use ($user) {
                        $profileQuery->where(
                            'division_id',
                            $user->profile?->division_id
                        );
                    });
            })

            ->latest('created_at')
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
        return JobSubmission::with(['employee', 'category'])
            ->whereDate('created_at', today())
            ->whereHas(
                'employee',
                fn($q) =>
                $q->role(['supervisor', 'staff'])
            )
            ->latest('created_at')
            ->get();
    }
    public function siteManagerApproveSupervisorAndStaffSubmission(int $id, string $status, string $comment = null)
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
        if ($submissions->status !== 'pending') {
            throw new \Exception('Submission already processed');
        }

        $submissions->update([
            'status' => $status,
            'approved_by' => Auth::id(),
            'comment' => $comment,
        ]);

        return $submissions->load(['employee', 'category']);
    }



    public function getWeeklySubmissionSummaryForCurrentUser(): array
    {
        $user = Auth::user();

        $today = Carbon::today();
        $startDate = $today->copy()->subDays(6);

        $submissions = JobSubmission::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total')
        )
            ->where('employee_id', $user->id) // 🔥 filter by logged-in user
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
    public function destroy(JobSubmission $submission): void
    {
        if ($submission->status !== 'pending') {
            throw new \Exception('Only pending submissions can be deleted');
        }

        $submission->delete();
    }
    public function getGardenerRankingByDivision()
    {
        $user = Auth::user();

        $ranking = JobSubmission::query()
            ->select(
                'employee_id',
                DB::raw('COUNT(*) as total_submissions')
            )
            ->with([
                'employee:id,name,division'
            ])
            ->whereHas('employee', function ($q) use ($user) {
                $q->whereHas('profile', function ($q) use ($user) {
                    $q->where('division_id', $user->profile->division_id);
                })
                    ->role('gardener');
            })
            ->groupBy('employee_id')
            ->orderByDesc('total_submissions')
            ->get();

        return [
            'data' => $ranking,
            'message' => $ranking->isEmpty()
                ? 'Ranking gardener tidak ditemukan'
                : 'Ranking gardener berhasil diambil'
        ];
    }
}
