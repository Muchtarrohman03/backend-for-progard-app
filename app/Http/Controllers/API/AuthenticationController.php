<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\JobSubmission;
use App\Models\Overtime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use PHPUnit\Util\PHP\Job;
use Spatie\Permission\Models\Role;

class AuthenticationController extends Controller
{
    /**
     * Register a new account.
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'     => 'required|string|min:4',
                'email'    => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]);

            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            return response()->json([
                'response_code' => 201,
                'status'        => 'success',
                'message'       => 'Successfully registered',
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'response_code' => 422,
                'status'        => 'error',
                'message'       => 'Validation failed',
                'errors'        => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Registration Error: ' . $e->getMessage());

            return response()->json([
                'response_code' => 500,
                'status'        => 'error',
                'message'       => 'Registration failed',
            ], 500);
        }
    }

    /**
     * Login and return auth token.
     */
    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string'
        ]);

        // Coba login
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.']
            ]);
        }

        $user = Auth::user();

        // Buat token — device_name untuk menghindari token ganda
        $token = $user->createToken($request->device_name ?? 'mobile')->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Login successful',
            'data' => [
                'username'    => $user->name,
                'role'        => $user->getRoleNames(),
                'access_token' => $token,
            ]
        ], 200);
    }


    /**
     * Get list of users (paginated) — protected route.
     */
    public function userInfo()
    {
        try {
            $users = User::latest()->paginate(10);

            return response()->json([
                'response_code'  => 200,
                'status'         => 'success',
                'message'        => 'Fetched user list successfully',
                'data_user_list' => $users,
            ]);
        } catch (\Exception $e) {
            Log::error('User List Error: ' . $e->getMessage());

            return response()->json([
                'response_code' => 500,
                'status'        => 'error',
                'message'       => 'Failed to fetch user list',
            ], 500);
        }
    }

    /**
     * Get user details — protected route.
     */
    public function myProfile(Request $request)
    {
        try {
            $user = $request->user();

            return response()->json([
                "status" => "success",
                "message" => "User profile fetched",
                "data" => [
                    "username" => $user->name,
                    "email" => $user->email,
                    "division" => $user->division, // langsung ambil dari kolom users
                    "role" => $user->getRoleNames(), // role list dari spatie
                    "gender" => $user->gender,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('User Profile Error: ' . $e->getMessage());

            return response()->json([
                'response_code' => 500,
                'status'        => 'error',
                'message'       => 'Failed to fetch user profile',
            ], 500);
        }
    }

    //get statoverview for user
    public function statOverview(Request $request)
    {
        try {
            $user = $request->user();
            $jobSubmissionStats = JobSubmission::where('employee_id', $user->id)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $absenceStats = Absence::where('employee_id', $user->id)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $overtimeStats = Overtime::where('employee_id', $user->id)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $detail = [
                'job_submissions' => [
                    'pending'  => $jobSubmissionStats['pending'] ?? 0,
                    'approved' => $jobSubmissionStats['approved'] ?? 0,
                    'rejected' => $jobSubmissionStats['rejected'] ?? 0,
                ],
                'absences' => [
                    'pending'  => $absenceStats['pending'] ?? 0,
                    'approved' => $absenceStats['approved'] ?? 0,
                    'rejected' => $absenceStats['rejected'] ?? 0,
                ],
                'overtime' => [
                    'pending'  => $overtimeStats['pending'] ?? 0,
                    'approved' => $overtimeStats['approved'] ?? 0,
                    'rejected' => $overtimeStats['rejected'] ?? 0,
                ],
            ];




            return response()->json([
                'status' => 'success',
                'message' => 'Pengambilan statistik user berhasil',
                'user' => $user->name,
                'data' => [
                    'job_submissions' => [
                        'total'    => $jobSubmissionStats->sum(),
                        'pending'  => $jobSubmissionStats['pending'] ?? 0,
                        'approved' => $jobSubmissionStats['approved'] ?? 0,
                        'rejected' => $jobSubmissionStats['rejected'] ?? 0,
                    ],
                    'absences' => [
                        'total'    => $absenceStats->sum(),
                        'pending'  => $absenceStats['pending'] ?? 0,
                        'approved' => $absenceStats['approved'] ?? 0,
                        'rejected' => $absenceStats['rejected'] ?? 0,
                    ],
                    'overtime' => [
                        'total'    => $overtimeStats->sum(),
                        'pending'  => $overtimeStats['pending'] ?? 0,
                        'approved' => $overtimeStats['approved'] ?? 0,
                        'rejected' => $overtimeStats['rejected'] ?? 0,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('User Statistics Error: ' . $e->getMessage());

            return response()->json([
                'status'        => 'error',
                'message'       => 'Gagal mengambil statistik user',
            ], 500);
        }
    }

    /**
     * Logout user and revoke tokens — protected route.
     */
    public function logOut(Request $request)
    {
        try {
            $user = $request->user();

            if ($user) {
                $user->tokens()->delete();

                return response()->json([
                    'response_code' => 200,
                    'status'        => 'success',
                    'message'       => 'Successfully logged out',
                ]);
            }

            return response()->json([
                'response_code' => 401,
                'status'        => 'error',
                'message'       => 'User not authenticated',
            ], 401);
        } catch (\Exception $e) {
            Log::error('Logout Error: ' . $e->getMessage());

            return response()->json([
                'response_code' => 500,
                'status'        => 'error',
                'message'       => 'An error occurred during logout',
            ], 500);
        }
    }
}
