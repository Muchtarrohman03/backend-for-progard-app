<?php

namespace App\Services;

use App\Models\Absence;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\Eloquent\Collection;

class AbsenceService
{
    private const APPROVAL_STATUSES = ['approved', 'rejected'];

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function authUser()
    {
        $user = Auth::user();

        if (!$user) {
            throw new HttpException(401, 'Unauthenticated');
        }

        return $user;
    }

    private function validateApprovalStatus(string $status): void
    {
        if (!in_array($status, self::APPROVAL_STATUSES)) {
            throw new HttpException(422, 'Invalid approval status');
        }
    }

    private function ensurePending(Absence $absence): void
    {
        if ($absence->status !== 'pending') {
            throw new HttpException(400, 'Absence already processed');
        }
    }

    private function ensureOwner(Absence $absence): void
    {
        if ($absence->employee_id !== $this->authUser()->id) {
            throw new HttpException(403, 'Unauthorized access');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Employee Methods
    |--------------------------------------------------------------------------
    */

    public function getMyAbsences(): Collection
    {
        return Absence::with('employee')
            ->where('employee_id', $this->authUser()->id)
            ->latest('created_at')
            ->get();
    }

    public function getMyAbsencesByDate(string $date): Collection
    {
        return Absence::with('employee')
            ->where('employee_id', $this->authUser()->id)
            ->whereDate('start', $date)
            ->latest('created_at')
            ->get();
    }

    public function getMyAbsenceById(int $id): Absence
    {
        $absence = Absence::with('employee')->findOrFail($id);

        $this->ensureOwner($absence);

        return $absence;
    }

    public function store(array $data): Absence
    {
        $user = $this->authUser();

        return Absence::create([
            'reason'      => $data['reason'],
            'start'       => $data['start'],
            'end'         => $data['end'],
            'description' => $data['description'] ?? null,
            'employee_id' => $user->id,
            'evidence'    => $data['evidence']?->store('absences/evidence', 'public'),
            'status'      => 'pending',
        ]);
    }

    public function destroy(int $id): void
    {
        $absence = Absence::findOrFail($id);

        $this->ensureOwner($absence);
        $this->ensurePending($absence);

        $absence->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | Supervisor Methods
    |--------------------------------------------------------------------------
    */

    public function spvSelectGardenerAbsencesToday(): Collection
    {
        $user = $this->authUser();

        return Absence::with('employee')
            ->whereDate('start', today())
            ->whereHas('employee', function ($q) use ($user) {
                $q->where('division', $user->division)
                    ->role('gardener');
            })
            ->latest('created_at')
            ->get();
    }

    public function spvApproveGardenerAbsence(int $absenceId, string $status): Absence
    {
        $this->validateApprovalStatus($status);

        $absence = Absence::with('employee')->findOrFail($absenceId);

        $this->ensurePending($absence);

        $authUser = $this->authUser();
        $employee = $absence->employee;

        if (!$employee->hasRole('gardener')) {
            throw new HttpException(403, 'Only gardener absence can be approved');
        }

        if ($employee->division !== $authUser->division) {
            throw new HttpException(403, 'Cannot approve absence from different division');
        }

        $absence->update([
            'status' => $status,
            'approved_by' => $authUser->id,
        ]);

        return $absence->fresh();
    }

    /*
    |--------------------------------------------------------------------------
    | Site Manager Methods
    |--------------------------------------------------------------------------
    */

    public function siteManagerSelectSpvAndStaffAbsencesToday(): Collection
    {
        return Absence::with('employee')
            ->whereDate('start', today())
            ->whereHas(
                'employee',
                fn($q) =>
                $q->role(['supervisor', 'staff'])
            )
            ->latest('created_at')
            ->get();
    }

    public function siteManagerApproveSpvAndStaffAbsence(int $absenceId, string $status): Absence
    {
        $this->validateApprovalStatus($status);

        $absence = Absence::with('employee')->findOrFail($absenceId);

        $this->ensurePending($absence);

        $employee = $absence->employee;

        if (!$employee->hasAnyRole(['supervisor', 'staff'])) {
            throw new HttpException(
                403,
                'Only supervisor or staff absence can be approved'
            );
        }

        $absence->update([
            'status' => $status,
            'approved_by' => $this->authUser()->id,
        ]);

        return $absence->fresh();
    }

    public function getAbsencesByDivisionAndDate(
        string $division,
        string $date
    ): Collection {
        return Absence::with('employee')
            ->whereDate('start', $date)
            ->whereHas('employee', function ($q) use ($division) {
                $q->where('division', $division);
            })
            ->latest('created_at')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | General Methods
    |--------------------------------------------------------------------------
    */

    public function getById(int $id): Absence
    {
        return Absence::with('employee')->findOrFail($id);
    }
}
