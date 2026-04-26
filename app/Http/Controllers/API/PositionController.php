<?php

namespace App\Http\Controllers\Api;

use App\Events\ForceLocationUpdateRequested;
use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;


class PositionController extends Controller
{
    // -----------------------------
    // Helper: ambil latest positions
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
    // Helper: transform response clean
    // -----------------------------
    private function transform($positions)
    {
        return $positions->map(function ($position) {
            return [
                'employee_id' => $position->employee_id,
                'name' => $position->employee->name ?? null,
                'role' => $position->employee->roles->pluck('name')->first(),
                'division' => $position->employee->division ?? null,
                'latitude' => (float) $position->latitude,
                'longitude' => (float) $position->longitude,
                'last_update' => $position->created_at,
            ];
        });
    }

    // -----------------------------
    // Semua user bisa kirim lokasi
    // -----------------------------
    public function store(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = Auth::user();
        $id = Auth::id();

        Position::create([
            'employee_id' => $id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json([
            'message' => 'Location stored',
            'employee_id' => $id,
            'name' => $user->name
        ]);
    }

    // -----------------------------
    // Gardener / Staff / Supervisor: lihat diri sendiri
    // -----------------------------
    public function selfPosition()
    {
        $user = Auth::user();
        $positions = $this->latestPositionsQuery()
            ->where('employee_id', $user->id)
            ->with(['employee:id,name,division', 'employee.roles:id,name'])
            ->get();

        return response()->json($this->transform($positions));
    }

    // -----------------------------
    // Supervisor: lihat gardener di division yang sama
    // -----------------------------
    public function divisionGardeners()
    {
        $user = Auth::user();
        $positions = $this->latestPositionsQuery()
            ->whereHas('employee', function ($q) use ($user) {
                $q->where('division', $user->division)
                    ->role('gardener');
            })
            ->with(['employee:id,name,division', 'employee.roles:id,name'])
            ->get();

        return response()->json($this->transform($positions));
    }

    // -----------------------------
    // Site Manager: lihat semua gardener/staff/supervisor
    // -----------------------------
    public function allPositions()
    {
        $positions = $this->latestPositionsQuery()
            ->whereHas('employee', function ($q) {
                $q->role(['gardener', 'staff', 'supervisor']);
            })
            ->with(['employee:id,name,division', 'employee.roles:id,name'])
            ->get();

        return response()->json($this->transform($positions));
    }

    // ------------------------------
    // Force update location (manual trigger)
    // ------------------------------
    public function forceUpdate(Request $request)
    {
        $actor = Auth::user();

        // ── Site Manager → paksa SEMUA role ──────────────────────────
        if ($actor->hasRole('site_manager')) {
            ForceLocationUpdateRequested::dispatch(
                actor: $actor,
                scope: 'global',
                division: null
            );

            return response()->json([
                'success' => true,
                'message' => 'Force location update dikirim ke semua user (gardener, staff, supervisor)',
            ]);
        }

        // ── Supervisor → hanya gardener di divisinya ──────────────────
        if ($actor->hasRole('supervisor')) {
            $division    = $actor->division;
            $divisionKey = str_replace(' ', '_', strtolower($division));

            ForceLocationUpdateRequested::dispatch(
                actor: $actor,
                scope: 'division',
                division: $divisionKey
            );

            return response()->json([
                'success'  => true,
                'message'  => 'Force location update dikirim ke gardener divisi',
                'division' => $divisionKey,
            ]);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // ------------------------------
    // Cek flag force update (dipanggil client setelah dapat notifikasi)
    // ------------------------------

    public function checkForceFlag(Request $request)
    {
        $user     = Auth::user();
        $cacheKey = "force_location:{$user->id}";
        $flag     = Cache::get($cacheKey);

        if (!$flag) {
            return response()->json(['force_update' => false]);
        }

        Cache::forget($cacheKey);

        return response()->json([
            'force_update' => true,
            'meta'         => $flag,
        ]);
    }
}
