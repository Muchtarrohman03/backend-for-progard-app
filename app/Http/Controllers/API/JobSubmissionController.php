<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\JobSubmission;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class JobSubmissionController extends Controller
{
    public function index()
    {
        $submissions = JobSubmission::with(['employee', 'category'])->get();

        $submissions->transform(function ($item) {
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
            'message' => 'List of job submissions retrieved successfully',
            'data' => $submissions,
        ]);
    }

    public function mysubmissions()
    {
        $user = Auth::user();

        $submissions = JobSubmission::with(['employee', 'category'])
            ->where('employee_id', $user->id)
            ->get();

        $submissions->transform(function ($item) {
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
            'message' => 'List of my job submissions retrieved successfully',
            'data' => $submissions,
        ]);
    }

    public function show($id)
    {
        $submission = JobSubmission::with(['employee', 'category'])->find($id);

        if (!$submission) {
            return response()->json([
                'response_code' => 404,
                'status' => 'error',
                'message' => 'Job submission not found',
            ]);
        }

        if ($submission->image_path) {
            $submission->image_url = URL::to(Storage::url($submission->image_path));
        } else {
            $submission->image_url = null;
        }

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Job submission details retrieved successfully',
            'data' => $submission,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:job_categories,id',
            'status' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $user = Auth::user();

        $path = null;
        if ($request->hasFile('image')) {
            // Simpan di storage/app/private/job_submission
            $path = $request->file('image')->store('job_submission', 'public');
        }

        $submission = JobSubmission::create([
            'category_id' => $validated['category_id'],
            'employee_id' => $user->id,
            'submitted_at' => now(),
            'status' => $validated['status'] ?? 'pending',
            'image_path' => $path,
        ]);

        return response()->json([
            'response_code' => 201,
            'status' => 'success',
            'message' => 'Job submission created successfully',
            'data' => $submission,
        ]);
    }

    public function update(Request $request, $id)
    {
        $submission = JobSubmission::find($id);

        if (!$submission) {
            return response()->json([
                'response_code' => 404,
                'status' => 'error',
                'message' => 'Job submission not found',
            ]);
        }

        $validated = $request->validate([
            'category_id' => 'sometimes|exists:job_categories,id',
            'status' => 'sometimes|string',
            'image' => 'sometimes|image|max:2048',
        ]);

        // Jika ada gambar baru
        if ($request->hasFile('image')) {
            if ($submission->image_path && Storage::exists($submission->image_path)) {
                Storage::delete($submission->image_path);
            }

            $submission->image_path = $request->file('image')->store('job_submission', 'public');
        }

        // Update category_id dan status jika ada
        if (isset($validated['category_id'])) {
            $submission->category_id = $validated['category_id'];
        }

        if (isset($validated['status'])) {
            $submission->status = $validated['status'];
        }

        $submission->save();

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Job submission updated successfully',
            'data' => $submission->fresh(),
        ]);
    }
    public function destroy($id)
    {
        $submission = JobSubmission::find($id);

        // cek apakah data ada
        if (!$submission) {
            return response()->json([
                'response_code' => 404,
                'status' => 'error',
                'message' => 'Job submission not found',
            ], 404);
        }

        // pastikan user yang login adalah pemilik data
        $user = Auth::user();
        if ($submission->employee_id !== $user->id) {
            return response()->json([
                'response_code' => 403,
                'status' => 'error',
                'message' => 'You are not authorized to delete this submission',
            ], 403);
        }

        // hapus file dari storage jika ada
        if ($submission->image_path && Storage::exists($submission->image_path)) {
            Storage::delete($submission->image_path);
        }

        // hapus data dari database
        $submission->delete();

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Job submission deleted successfully',
        ]);
    }

    public function divisionSubmissions()
    {
        $user = Auth::user();
        $submissions = JobSubmission::with([
            'employee:id,name,division',
            'category:id,name',
        ])
            ->whereHas('employee', function ($q) use ($user) {
                $q->where('division', $user->division);
            })
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($item) {
                return $item;
            });

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Submissions retrieved successfully',
            'data' => $submissions,
        ]);
    }

    public function submissionSummary(Request $request)
    {
        $user = Auth::user();

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $todayCount = JobSubmission::where('employee_id', $user->id)
            ->whereDate('submitted_at', $today)
            ->count();

        $yesterdayCount = JobSubmission::where('employee_id', $user->id)
            ->whereDate('submitted_at', $yesterday)
            ->count();

        // Jika belum pernah submit sama sekali
        if ($todayCount === 0 && $yesterdayCount === 0) {
            return response()->json([
                'status' => 'success',
                'message' => 'Belum ada job submission yang dibuat',
                'data' => [
                    'today' => 0,
                    'yesterday' => 0,
                    'trend' => 'no_data'
                ]
            ]);
        }

        if ($todayCount > $yesterdayCount) {
            $trend = 'increase';
            $description = 'Lebih banyak dari kemarin';
        } elseif ($todayCount < $yesterdayCount) {
            $trend = 'decrease';
            $description = 'Lebih sedikit dari kemarin';
        } else {
            $trend = 'same';
            $description = 'Jumlah sama dengan kemarin';
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Ringkasan job submission',
            'data' => [
                'today' => $todayCount,
                'yesterday' => $yesterdayCount,
                'trend' => $trend,
                'description' => $description
            ]
        ]);
    }
}
