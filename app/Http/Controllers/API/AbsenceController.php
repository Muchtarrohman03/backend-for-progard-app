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

    public function myabsences()
    {
        $user = Auth::user();

        $absences = Absence::with('employee')
            ->where('employee_id', $user->id)
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
            'message' => 'List of my absences retrieved successfully',
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
            'description' => 'nullable|string',
            'status' => 'required|in:pending,approved,rejected',

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
            'status' => $validated['status'] ?? 'pending',
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
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
            'start' => 'sometimes|date',
            'end' => 'sometimes|date|after_or_equal:start',
            'reason' => 'sometimes|in:sakit,darurat,lainnya',
            'evidence' => 'sometimes|nullable|image|max:2048',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:pending,approved,rejected',
        ]);

        // Hapus file lama jika ada file baru
        if ($request->hasFile('evidence')) {
            if ($absence->evidence && Storage::exists($absence->evidence)) {
                Storage::delete($absence->evidence);
            }

            // Simpan file baru
            $absence->evidence = $request->file('evidence')->store('absences', 'public');
        }

        // Update data lain
        $absence->fill([
            'start' => $validated['start'] ?? $absence->start,
            'end' => $validated['end'] ?? $absence->end,
            'reason' => $validated['reason'] ?? $absence->reason,
            'description' => $validated['description'] ?? $absence->description,
            'status' => $validated['status'] ?? $absence->status,
        ]);

        $absence->save();

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Absence updated successfully',
            'data' => $absence,
        ], 200);
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
}
