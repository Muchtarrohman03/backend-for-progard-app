<?php

namespace App\Services;

use App\Models\Overtime;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OvertimeService
{

    public function getMyOvertimes()
    {
        return Overtime::with([
            'employee.profile.division',
            'approver.profile',
            'category'
        ]);
    }

    public function getById(int $id)
    {
        return Overtime::with(['employee', 'category'])
            ->findOrFail($id);
    }
    public function getByDate(string $date)
    {
        return Overtime::with(['employee', 'category'])
            ->where('employee_id', Auth::id())
            ->whereDate('created_at', $date)
            ->get();
    }

    public function store($request)
    {
        $beforePath = $request->file('before')
            ->store('overtime/before', 'public');

        $afterPath = $request->file('after')
            ->store('overtime/after', 'public');

        return Overtime::create([
            'start' => $request->start,
            'end' => $request->end,
            'category_id' => $request->category_id,
            'employee_id' => Auth::id(),
            // 'submitted_at' => now(),
            'description' => $request->description,
            'before' => $beforePath,
            'after' => $afterPath,
            'status' => 'pending'
        ]);
    }
    public function getByDivisionAndDate(string $division, string $date)
    {
        return Overtime::query()

            ->whereHas('employee.profile.division', function ($q) use ($division) {

                $q->where('name', $division);
            })

            ->whereDate('created_at', $date)

            ->latest('created_at')

            ->get();
    }

    public function spvSelectToday()
    {
        $user = Auth::user();

        return Overtime::with(['employee', 'category'])
            ->whereDate('created_at', today())
            ->whereHas('employee', function ($q) use ($user) {
                $q->whereHas('profile', function ($q) use ($user) {
                    $q->where('division_id', $user->profile->division_id);
                })
                    ->role('gardener');
            })
            ->latest('created_at')
            ->get();
    }

    public function siteManagerSelectToday()
    {
        return Overtime::with(['employee', 'category'])
            ->whereDate('created_at', today())
            ->whereHas(
                'employee',
                fn($q) =>
                $q->role(['supervisor', 'staff'])
            )
            ->latest('created_at')
            ->get();
    }

    public function approvalSpv(int $id, string $status, ?string $comment)
    {
        $authUser = Auth::user();

        $overtime = Overtime::with('employee')
            ->findOrFail($id);
        if ($overtime->status !== 'pending') {
            throw new HttpException(
                400,
                'Only pending overtime can be approved'
            );
        }

        // cek apakah employee adalah gardener
        if (!$overtime->employee->hasRole('gardener')) {
            throw new HttpException(
                403,
                'Only gardener overtime can be approved'
            );
        }

        // cek apakah division sama
        if (
            $overtime->employee?->profile?->division_id
            !==
            $authUser?->profile?->division_id
        ) {
            throw new HttpException(
                403,
                'You cannot approve overtime from different division'
            );
        }

        $overtime->update([
            'status' => $status,
            'approved_by' => Auth::id(),
            'comment' => $comment,
        ]);

        return $overtime->load(['employee', 'category']);
    }
    public function approvalSiteManager(int $id, string $status, ?string $comment)
    {

        $overtime = Overtime::with('employee')
            ->findOrFail($id);

        //jika status bukan pending maka tidak bisa approve
        if ($overtime->status !== 'pending') {
            throw new HttpException(
                400,
                'Only pending overtime can be approved'
            );
        }

        //jika role employee bukan supervisor maka tidak bisa approve
        if (!$overtime->employee->hasAnyRole(['supervisor', 'staff'])) {
            throw new HttpException(
                403,
                'Only supervisor or staff overtime can be approved'
            );
        }

        $overtime->update([
            'status' => $status,
            'approved_by' => Auth::id(),
            'comment' => $comment,
        ]);

        return $overtime->load(['employee', 'category']);
    }

    public function delete(int $id): void
    {
        Overtime::findOrFail($id)->delete();
    }
}
