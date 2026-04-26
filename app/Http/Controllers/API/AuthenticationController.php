<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Http\Requests\Authentication\LoginRequest;
use App\Services\AuthenticationService;
use Illuminate\Http\Request;

class AuthenticationController extends Controller
{
    public function __construct(
        private AuthenticationService $service
    ) {}

    public function login(LoginRequest $request)
    {
        $result = $this->service->login($request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Login successful',
            'data' => [
                'username'     => $result['user']->name,
                'role'         => $result['user']->roles->pluck('name')->toArray(),
                'division'     => $result['division'],
                'access_token' => $result['token'],
            ]
        ]);
    }

    public function myProfile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status'  => 'success',
            'message' => 'User profile fetched',
            'data' => [
                'username' => $user->name,
                'email'    => $user->email,
                'division' => $user->division,
                'role'     => $user->getRoleNames(),
                'gender'   => $user->gender,
            ]
        ]);
    }

    public function statOverview(Request $request)
    {
        $stats = $this->service->statOverview($request->user());

        return response()->json([
            'status'  => 'success',
            'message' => 'Pengambilan statistik user berhasil',
            'data'    => $stats
        ]);
    }

    public function logout(Request $request)
    {
        $this->service->logout($request->user());

        return response()->json([
            'status'  => 'success',
            'message' => 'Successfully logged out',
        ]);
    }
}
