<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PositionController extends Controller
{
    // -----------------------------
    // Helper: subquery ambil latest position per employee
    // -----------------------------
    private function latestPositionsQuery()
    {
        return Position::whereIn('id', function ($sub) {
            $sub->selectRaw('MAX(id)')
                ->from('positions')
                ->groupBy('employee_id');
        });
    }

    // -----------------------------
    // Helper: eager load yang benar
    // Sertakan 'id' agar relasi roles bisa di-resolve
    // -----------------------------
    private function withEmployee()
    {
        return ['employee:id,name,division', 'employee.roles:id,name'];
    }

    // -----------------------------
    // Helper: transform response
    // -----------------------------
    private function transform($positions): array
    {
        return $positions->map(fn($position) => [
            'employee_id' => $position->employee_id,
            'name'        => $position->employee->name ?? null,
            'role'        => $position->employee->roles->pluck('name')->first() ?? null,
            'division'    => $position->employee->division ?? null,
            'latitude'    => (float) $position->latitude,
            'longitude'   => (float) $position->longitude,
            'accuracy'    => $position->accuracy,
            'speed'       => $position->speed,
            'heading'     => $position->heading,
            'last_update' => $position->updated_at,
        ])->values()->all();
    }

    // -----------------------------
    // Helper: wrapper response standar
    // -----------------------------
    private function success(mixed $data, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    // -----------------------------
    // Semua user bisa kirim lokasi
    // -----------------------------
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy'  => 'nullable|numeric|min:0',
            'speed'     => 'nullable|numeric|min:0',
            'heading'   => 'nullable|numeric|between:0,360',
        ]);

        $user = Auth::user();

        Position::create([
            'employee_id' => $user->id,
            'latitude'    => $validated['latitude'],
            'longitude'   => $validated['longitude'],
            'accuracy'    => $validated['accuracy'] ?? null,
            'speed'       => $validated['speed'] ?? null,
            'heading'     => $validated['heading'] ?? null,
        ]);

        return $this->success([
            'employee_id' => $user->id,
            'name'        => $user->name,
        ], 'Location stored', 201);
    }

    // -----------------------------
    // Gardener / Staff / Supervisor: lihat posisi diri sendiri
    // -----------------------------
    public function selfPosition(): JsonResponse
    {
        $positions = $this->latestPositionsQuery()
            ->where('employee_id', Auth::id())
            ->with($this->withEmployee())
            ->get();

        return $this->success($this->transform($positions));
    }

    // -----------------------------
    // Supervisor: lihat gardener di division yang sama
    // -----------------------------
    public function divisionGardeners(): JsonResponse
    {
        $user = Auth::user();

        $positions = $this->latestPositionsQuery()
            ->whereHas('employee', function ($q) use ($user) {
                $q->where('division', $user->division)
                    ->role('gardener');
            })
            ->with($this->withEmployee())
            ->get();

        return $this->success($this->transform($positions));
    }

    // -----------------------------
    // Site Manager: lihat semua gardener / staff / supervisor
    // -----------------------------
    public function allPositions(): JsonResponse
    {
        $positions = $this->latestPositionsQuery()
            ->whereHas('employee', fn($q) => $q->role(['gardener', 'staff', 'supervisor']))
            ->with($this->withEmployee())
            ->get();

        return $this->success($this->transform($positions));
    }
}
