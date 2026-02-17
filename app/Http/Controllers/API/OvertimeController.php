<?php

namespace App\Http\Controllers\Api;

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

    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'start' => 'required|date_format:H:i',
            'end' => 'required|date_format:H:i|after:start',
            'category_id' => 'required|exists:job_categories,id',
            'description' => 'nullable|string',
            'before' => 'required|image|max:2048',
            'after' => 'required|image|max:2048',
        ]);

        $user = Auth::user();

        $beforePath = $request->file('before')->store('overtime/before', 'public');
        $afterPath = null;
        if ($request->hasFile('after')) {
            $afterPath = $request->file('after')->store('overtime/after', 'public');
        }

        $overtime = Overtime::create([
            'start' => $validated['start'],
            'end' => $validated['end'],
            'submitted_at' => now(),
            'category_id' => $validated['category_id'],
            'employee_id' => $user->id,
            'description' => $validated['description'] ?? null,
            'before' => $beforePath,
            'after' => $afterPath,
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

    //Mengambil data Lembur milik user berdasarkan tanggal
    public function getOvertimesByDate(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $user = Auth::user();
        $date = $request->input('date');

        $overtimes = Overtime::with(['employee', 'category'])
            ->where('employee_id', $user->id)
            ->whereDate('submitted_at', $date)
            ->get();

        $overtimes->transform(function ($item) {
            $item->before_url = $item->before
                ? url(Storage::url($item->before))
                : null;

            $item->after_url = $item->after
                ? url(Storage::url($item->after))
                : null;

            return $item;
        });

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => $overtimes->isEmpty()
                ? 'Tidak Menemukan Data Lembur'
                : 'Berhasil Menemukan Data Lembur',
            'data' => $overtimes,
        ]);
    }
    /**
     * Mendapatkan data lembur berdasarkan divisi utuk supervisor
     */
    public function spvSelectOvertime()
    {
        $authUser = Auth::user();

        $todayStart = now()->startOfDay();
        $todayEnd   = now()->endOfDay();

        $overtimes = Overtime::query()
            ->select([
                'id',
                'start',
                'end',
                'employee_id',
                'category_id',
                'status',
                'before',
                'after',
                'submitted_at',
                'created_at'
            ])
            ->with([
                'employee:id,name,division',
                'category:id,name',
            ])
            ->whereBetween('submitted_at', [$todayStart, $todayEnd])
            ->whereHas('employee', function ($query) use ($authUser) {
                $query->where('division', $authUser->division)
                    ->role('gardener');
            })
            ->latest('submitted_at')
            ->get();
        $overtimes->transform(function ($item) {
            $item->before_url = $item->before
                ? url(Storage::url($item->before))
                : null;

            $item->after_url = $item->after
                ? url(Storage::url($item->after))
                : null;

            return $item;
        });
        return response()->json([
            'response_code' => 200,
            'status'        => 'success',
            'message'       => $overtimes->isEmpty()
                ? 'Data Lembur Hari Ini Tidak Ditemukan'
                : 'Data Lembur Hari Ini Ditemukan',
            'data'          => $overtimes,
        ]);
    }
    /**
     * Update status lembur
     */
    public function spvAprovalOvertime(Request $request, string $id)
    {
        $overtime = Overtime::find($id);
        if (!$overtime) {
            return response()->json([
                'response_code' => 404,
                'status' => 'error',
                'message' => 'Overtime not found',
            ]);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $overtime->status = $validated['status'];
        $overtime->save();

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Overtime status updated successfully',
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
