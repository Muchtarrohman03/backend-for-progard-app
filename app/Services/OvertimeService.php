<?php

namespace App\Services;

use App\Models\Overtime;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OvertimeService
{

    public function getMyOvertimes()
    {
        return Overtime::with(['employee', 'category'])
            ->where('employee_id', Auth::id())
            ->latest()
            ->get();
    }

    public function getById(int $id)
    {
        return Overtime::with(['employee', 'category'])
            ->findOrFail($id);
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
            'submitted_at' => now(),
            'description' => $request->description,
            'before' => $beforePath,
            'after' => $afterPath,
            'status' => 'pending'
        ]);
    }
    public function getByDivisionAndDate(string $division, string $date)
    {
        return Overtime::with(['employee', 'category'])
            ->whereRelation('employee', 'division', $division)
            ->whereDate('submitted_at', $date)
            ->latest('submitted_at')
            ->get();
    }
    public function getByDate(string $date)
    {
        return Overtime::with(['employee', 'category'])
            ->where('employee_id', Auth::id())
            ->whereDate('submitted_at', $date)
            ->latest()
            ->get();
    }

    public function spvSelectToday()
    {
        $user = Auth::user();

        return Overtime::with(['employee', 'category'])
            ->whereDate('submitted_at', today())
            ->whereHas('employee', function ($q) use ($user) {
                $q->where('division', $user->division)
                    ->role('gardener');
            })
            ->latest('submitted_at')
            ->get();
    }

    public function siteManagerSelectToday()
    {
        return Overtime::with(['employee', 'category'])
            ->whereDate('submitted_at', today())
            ->whereHas(
                'employee',
                fn($q) =>
                $q->role(['supervisor', 'staff'])
            )
            ->latest('submitted_at')
            ->get();
    }

    public function approvalSpv(int $id, string $status)
    {
        $authUser = Auth::user();

        $overtime = Overtime::with('employee')
            ->findOrFail($id);

        // cek apakah employee adalah gardener
        if (!$overtime->employee->hasRole('gardener')) {
            throw new HttpException(
                403,
                'Only gardener overtime can be approved'
            );
        }

        // cek apakah division sama
        if ($overtime->employee->division !== $authUser->division) {
            throw new HttpException(
                403,
                'You cannot approve overtime from different division'
            );
        }

        $overtime->update([
            'status' => $status,
            'approved_by' => Auth::id(),
        ]);

        return $overtime->load(['employee', 'category']);
    }
    public function approvalSiteManager(int $id, string $status)
    {

        $overtime = Overtime::with('employee')
            ->findOrFail($id);

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
        ]);

        return $overtime->load(['employee', 'category']);
    }

    public function delete(int $id): void
    {
        Overtime::findOrFail($id)->delete();
    }
}
