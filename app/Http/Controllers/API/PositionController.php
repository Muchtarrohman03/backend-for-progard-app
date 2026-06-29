<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Position;

use App\Services\PositionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



class PositionController extends Controller
{
    public function __construct(
        protected PositionService $positionService
    ) {}
    // -----------------------------
    // Helper: ambil latest positions
    // -----------------------------
    private function latestPositionsQuery()
    {
        return Position::whereIn('id', function ($sub) {
            $sub->selectRaw('MAX(created_at)')
                ->from('positions')
                ->groupBy('employee_id');
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

        $this->positionService->storePosition(
            Auth::user(),
            $request->latitude,
            $request->longitude
        );
        return response()->json([

            'message' => 'Location stored',
            'employee_id' => Auth::id(),
            'name' => Auth::user()->name,
        ]);
    }

    // -----------------------------
    // Gardener / Staff / Supervisor: lihat diri sendiri
    // -----------------------------
    public function selfPosition()
    {
        return response()->json(

            $this->positionService
                ->getSelfPosition(Auth::user())

        );
    }

    // -----------------------------
    // Supervisor: lihat gardener di division yang sama
    // -----------------------------
    public function divisionGardeners()
    {
        return response()->json(

            $this->positionService
                ->getDivisionGardeners(Auth::user())

        );
    }

    // -----------------------------
    // Site Manager: lihat semua gardener/staff/supervisor
    // -----------------------------
    public function allPositions()
    {
        return response()->json(

            $this->positionService
                ->getAllPositions()

        );
    }
}
