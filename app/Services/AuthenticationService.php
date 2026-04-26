<?php

namespace App\Services;

use App\Models\User;
use App\Models\Absence;
use App\Models\Overtime;
use App\Models\JobSubmission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticationService
{
    public function register(array $data)
    {
        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    public function login(array $credentials)
    {
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.']
            ]);
        }

        $user = Auth::user();

        $division = $user->division;

        $token = $user->createToken(
            $credentials['device_name'] ?? 'mobile'
        )->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'division' => $division,
        ];
    }

    public function logout($user)
    {
        $user->tokens()->delete();
    }

    public function statOverview($user)
    {
        return [
            'job_submissions' => $this->buildStats(JobSubmission::class, $user->id),
            'absences'        => $this->buildStats(Absence::class, $user->id),
            'overtime'        => $this->buildStats(Overtime::class, $user->id),
        ];
    }

    private function buildStats($model, $userId)
    {
        $stats = $model::where('employee_id', $userId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total'    => $stats->sum(),
            'pending'  => $stats['pending'] ?? 0,
            'approved' => $stats['approved'] ?? 0,
            'rejected' => $stats['rejected'] ?? 0,
        ];
    }
}
