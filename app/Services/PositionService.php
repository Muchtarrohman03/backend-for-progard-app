<?php

namespace App\Services;

use App\Jobs\CleanExpiredPositionsJob;
use App\Models\Position;
use App\Models\User;

class PositionService
{
    public function transform($positions)
    {
        return $positions->map(function ($position) {
            return [
                'employee_id' => $position->employee_id,
                'name' => $position->employee->name,
                'role' => $position->employee
                    ->roles
                    ->pluck('name')
                    ->first(),
                'division' => $position->employee->division,
                'latitude' => (float)$position->latitude,
                'longitude' => (float)$position->longitude,
                'last_update' => $position->created_at,
            ];
        });
    }
    /**
     * Dispatch job untuk membersihkan posisi lama.
     */
    public function cleanExpiredPositions(): void
    {
        CleanExpiredPositionsJob::dispatch();
    }

    public function storePosition(User $user, float $latitude, float $longitude): Position
    {
        return Position::create([
            'employee_id' => $user->id,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }

    public function getSelfPosition(User $user)
    {
        $positions = Position::query()
            ->where('employee_id', $user->id)
            ->with([
                'employee',
                'employee.roles'
            ])

            ->latest('created_at')
            ->limit(1)
            ->get();
        return $this->transform($positions);
    }
    public function getDivisionGardeners(User $user)
    {
        $users = User::query()
            ->whereHas('profile', function ($query) use ($user) {
                $query->where(
                    'division_id',
                    $user->division_id
                );
            })
            ->role('gardener')
            ->with([
                'roles',
                'latestPosition'
            ])
            ->get();
        return $users
            ->filter(fn($u) => $u->latestPosition)
            ->map(function ($user) {
                $position = $user->latestPosition;
                return [
                    'employee_id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->roles
                        ->pluck('name')
                        ->first(),
                    'division' => $user->division,
                    'latitude' => (float)$position->latitude,
                    'longitude' => (float)$position->longitude,
                    'last_update' => $position->created_at,
                ];
            })
            ->values();
    }
    public function getAllPositions()
    {
        return User::query()
            ->role([
                'gardener',
                'staff',
                'supervisor'
            ])
            ->with([
                'roles',
                'latestPosition'
            ])
            ->get()
            ->filter(fn($user) => $user->latestPosition)
            ->map(function ($user) {

                $position = $user->latestPosition;

                return [
                    'employee_id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->roles->pluck('name')->first(),
                    'division' => $user->division,
                    'latitude' => (float) $position->latitude,
                    'longitude' => (float) $position->longitude,
                    'last_update' => $position->created_at,
                ];
            })
            ->values();
    }
}
