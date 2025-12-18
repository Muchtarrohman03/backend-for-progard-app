<?php

namespace App\Http\Controllers\Api;

use Override;
use App\Models\Overtime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OvertimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil data overtimes beserta relasinya
        $user = Auth::user();
        $overtimes = Overtime::with(['employee', 'category'])->where('employee_id', $user->id)->get();

        // Ubah image_path menjadi full URL (jika ada)
        $overtimes->transform(function ($item) {
            if ($item->image_path) {
                $item->image_url = URL::to(Storage::url($item->image_path));
            } else {
                $item->image_url = null;
            }
            return $item;
        });

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'List of overtimes retrieved successfully',
            'data' => $overtimes,
        ]);
    }
    public function myovertimes()
    {
        $user = Auth::user();

        $overtimes = Overtime::with(['employee', 'category'])
            ->where('employee_id', $user->id)
            ->get();

        $overtimes->transform(function ($item) {
            if ($item->image_path) {
                $item->image_url = URL::to(Storage::url($item->image_path));
            } else {
                $item->image_url = null;
            }
            return $item;
        });

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'List of my overtimes retrieved successfully',
            'data' => $overtimes,
        ]);
    }

    public function show($id)
    {
        // Ambil data lembur berdasarkan ID
        $overtime = Overtime::with(['employee', 'category'])->find($id);

        if (!$overtime) {
            return response()->json([
                'response_code' => 404,
                'status' => 'error',
                'message' => 'Overtime not found',
            ]);
        }

        // Tambahkan URL gambar (jika ada)
        if ($overtime->image_path) {
            $overtime->image_url = URL::to(Storage::url($overtime->image_path));
        } else {
            $overtime->image_url = null;
        }

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Overtime details retrieved successfully',
            'data' => $overtime,
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
            'start' => 'required|date_format:H:i',
            'end' => 'required|date_format:H:i|after:start',
            'category_id' => 'required|exists:job_categories,id',
            'status' => 'required|in:pending,approved,rejected',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $user = Auth::user();

        $path = null;
        if ($request->hasFile('image')) {
            // Simpan di storage/app/private/overtime
            $path = $request->file('image')->store('overtime', 'public');
        }

        $overtime = Overtime::create([
            'start' => $validated['start'],
            'end' => $validated['end'],
            'submitted_at' => now(),
            'category_id' => $validated['category_id'],
            'employee_id' => $user->id,
            'status' => $validated['status'] ?? 'pending',
            'description' => $validated['description'] ?? null,
            'image_path' => $path,
        ]);

        return response()->json(
            [
                'response_code' => 201,
                'status' => 'success',
                'message' => 'Overtime request created successfully',
                'data' => $overtime,
            ]
        );
    }

    /**
     * Display the specified resource.
     */

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
        //
        $overtime = Overtime::find($id);

        if (!$overtime) {
            return response()->json([
                'response_code' => 404,
                'status' => 'error',
                'message' => 'Overtime not found',
            ]);
        }

        $validated = $request->validate([
            'start' => 'sometimes|required|date_format:H:i',
            'end' => 'sometimes|required|date_format:H:i|after:start',
            'category_id' => 'sometimes|required|exists:job_categories,id',
            'status' => 'sometimes|required|in:pending,approved,rejected',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);
        if ($request->hasFile('image')) {
            // Simpan di storage/app/private/overtime
            $path = $request->file('image')->store('overtime', 'public');
            $overtime->image_path = $path;
        }

        $overtime->fill([
            'start' => $validated['start'] ?? $overtime->start,
            'end' => $validated['end'] ?? $overtime->end,
            'category_id' => $validated['category_id'] ?? $overtime->category_id,
            'status' => $validated['status'] ?? $overtime->status,
            'description' => $validated['description'] ?? $overtime->description,
        ]);
        $overtime->save();

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Overtime updated successfully',
            'data' => $overtime,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $overtime = Overtime::find($id);
        if (!$overtime) {
            return response()->json([
                'response_code' => 404,
                'status' => 'error',
                'message' => 'Overtime not found',
            ]);
        }
        $overtime->delete();
        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Overtime deleted successfully',
        ]);
    }
}
