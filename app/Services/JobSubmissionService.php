<?php

namespace App\Services;

use App\Models\JobSubmission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class JobSubmissionService
{
    public function getAll()
    {
        return JobSubmission::with(['employee', 'category'])
            ->latest()
            ->get()
            ->map(fn($item) => $this->appendBeforeAfterUrl($item));
    }

    public function getByDate($date)
    {
        return JobSubmission::with(['employee', 'category'])
            ->where('employee_id', Auth::id())
            ->whereDate('submitted_at', $date)
            ->get()
            ->map(fn($item) => $this->appendBeforeAfterUrl($item));
    }

    public function store($data)
    {
        $beforePath = $data['before']->store('job_submissions/before', 'public');
        $afterPath = $data['after']->store('job_submissions/after', 'public');

        return JobSubmission::create([
            'category_id' => $data['category_id'],
            'employee_id' => Auth::id(),
            'submitted_at' => now(),
            'status' => $data['status'] ?? 'pending',
            'before' => $beforePath,
            'after' => $afterPath,
        ]);
    }

    public function approve($submission, $status)
    {
        if ($submission->status !== 'pending') {
            throw new \Exception('Submission already processed');
        }

        $submission->update(['status' => $status]);

        return $submission->fresh();
    }

    public function getTodaySubmissionsByDivision(): array
    {
        $authUser = Auth::user();

        $todayStart = now()->startOfDay();
        $todayEnd   = now()->endOfDay();

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
            ->whereBetween('submitted_at', [$todayStart, $todayEnd])
            ->whereHas('employee', function ($query) use ($authUser) {
                $query->where('division', $authUser->division)
                    ->role('gardener');
            })
            ->latest('submitted_at')
            ->get()
            ->map(fn($item) => $this->appendBeforeAfterUrl($item));

        return [
            'data' => $submissions,
            'message' => $submissions->isEmpty()
                ? 'Data Laporan Kerja Hari Ini Tidak Ditemukan'
                : 'Data Laporan Kerja Hari Ini Ditemukan'
        ];
    }


    public function summary()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $todayCount = JobSubmission::where('employee_id', Auth::id())
            ->whereDate('submitted_at', $today)
            ->count();

        $yesterdayCount = JobSubmission::where('employee_id', Auth::id())
            ->whereDate('submitted_at', $yesterday)
            ->count();

        return compact('todayCount', 'yesterdayCount');
    }


    private function appendBeforeAfterUrl($item)
    {
        $item->before_url = $item->before
            ? url(Storage::url($item->before))
            : null;

        $item->after_url = $item->after
            ? url(Storage::url($item->after))
            : null;

        return $item;
    }
}
