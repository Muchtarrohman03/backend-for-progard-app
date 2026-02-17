<?php

namespace App\Http\Controllers\Api;

use App\Models\Absence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AbsenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $absences = Absence::with('employee')->get();

        $absences->transform(function ($item) {
            if ($item->evidence) {
                $item->image_url = URL::to(Storage::url($item->evidence));
            } else {
                $item->image_url = null;
            }
            return $item;
        });
        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'List of absences retrieved successfully',
            'data' => $absences,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
            'reason' => 'required|in:sakit,darurat,lainnya',
            'evidence' => 'required|image|max:2048',
            'description' => 'required|string',
        ]);
        $user = Auth::user();

        $path = null;
        if ($request->hasFile('evidence')) {
            // Simpan di storage/app/private/job_submission
            $path = $request->file('evidence')->store('absences', 'public');
        }
        $absences = Absence::create([
            'employee_id' => $user->id,
            'start' => $validated['start'],
            'end' => $validated['end'],
            'description' => $validated['description'] ?? null,
            'reason' => $validated['reason'],
            'evidence' => $path,
            'status' => 'pending',
        ]);

        return response()->json([
            'response_code' => 201,
            'status' => 'success',
            'message' => 'Absence created successfully',
            'data' => $absences,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $absence = Absence::with('employee')->find($id);


        if (!$absence) {
            return response()->json([
                'response_code' => 404,
                'status' => 'error',
                'message' => 'Absence not found',
            ]);
        }

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Absence details retrieved successfully',
            'data' => $absence,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the status of the specified resource in storage.
     */
    public function spvApprovalAbsence(Request $request, string $id)
    {
        $absence = Absence::find($id);

        if (!$absence) {
            return response()->json([
                'response_code' => 404,
                'status' => 'error',
                'message' => 'Absence not found',
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,approved,rejected',
        ]);

        if ($absence->status !== 'pending') {
            return response()->json([
                'response_code' => 400,
                'status' => 'error',
                'message' => 'Absence status already processed and cannot be updated',
            ], 400);
        }

        // Update hanya status
        $absence->update([
            'status' => $validated['status'] ?? $absence->status,
        ]);

        $absence->save();

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Absence status updated successfully',
            'data' => $absence->fresh(),
        ], 200);
    }
    /**
     * Mendapatkan data izin berdasarkan divisi dan tanggal terbaru untuk role supervisor
     */
    public function spvSelectAbsences(Request $request)
    {
        $user = Auth::user();
        $todayStart = now()->startOfDay();
        $todayEnd   = now()->endOfDay();

        $absences = Absence::query()->select([
            'id',
            'start',
            'end',
            'reason',
            'description',
            'evidence',
            'status',
            'submitted_at',
            'created_at',
        ])->with('employee:id,name,division')
            ->whereHas('employee', function ($query) use ($user) {
                $query->where('division', $user->division)->role('gardener');
            })
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->latest('submitted_at')
            ->get();

        $absences->transform(function ($item) {
            if ($item->evidence) {
                $item->image_url = URL::to(Storage::url($item->evidence));
            } else {
                $item->image_url = null;
            }
            return $item;
        });

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => $absences->isEmpty()
                ? 'Tidak Menemukan Data Absensi'
                : 'Berhasil Menemukan Data Absensi',
            'data' => $absences,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $absence = Absence::find($id);
        if (!$absence) {
            return response()->json([
                'response_code' => 404,
                'status' => 'error',
                'message' => 'Absence not found',
            ], 404);
        }
        if ($absence->evidence && Storage::exists($absence->evidence)) {
            Storage::delete($absence->evidence);
        }
        $absence->delete();
        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Absence deleted successfully',
        ], 200);
    }

    public function getMyAbsencesByDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $user = Auth::user();
        $date = $request->input('date');

        $absences = Absence::with('employee')
            ->where('employee_id', $user->id)
            ->whereDate('created_at', $date)
            ->get();

        $absences->transform(function ($item) {
            if ($item->evidence) {
                $item->image_url = URL::to(Storage::url($item->evidence));
            } else {
                $item->image_url = null;
            }
            return $item;
        });

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => $absences->isEmpty()
                ? 'Tidak Menemukan Data Absensi'
                : 'Berhasil Menemukan Data Absensi',
            'data' => $absences,
        ]);
    }
}
