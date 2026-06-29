<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AuthenticationService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class AuthenticationController extends Controller
{
    public function __construct(
        private AuthenticationService $service
    ) {}

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email'       => ['required', 'email'],
                'password'    => ['required', 'string'],
                'device_name' => ['nullable', 'string'],
            ]);

            $result = $this->service->login($validated);

            return response()->json([
                'status'  => 'success',
                'message' => 'Login successful',
                'data'    => [
                    'username'     => $result['user']->name,
                    'role'         => $result['user']->roles->pluck('name')->toArray(),
                    'division'     => $result['division'],
                    'access_token' => $result['token'],
                ]
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, [
                'validation' => 'Login request validation failed.',
                'authentication' => 'Login failed because you are not authenticated.',
                'not_found' => 'Login failed because the user account could not be found.',
                'server' => 'Login failed. Please try again later.',
            ]);
        }
    }

    public function myProfile(Request $request)
    {
        try {
            $user = $this->authenticatedUser($request);

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
        } catch (Throwable $e) {
            return $this->errorResponse($e, [
                'authentication' => 'Unable to fetch profile because you are not authenticated.',
                'not_found' => 'Unable to fetch profile because the user account could not be found.',
                'server' => 'Unable to fetch user profile. Please try again later.',
            ]);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $user = $this->authenticatedUser($request);

            $validated = $request->validate([
                'current_password' => ['required'],
                'new_password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $this->service->changePassword(
                $user,
                $validated
            );

            return response()->json([
                'message' => 'Password changed successfully. Please login again.'
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, [
                'validation' => 'Change password request validation failed.',
                'authentication' => 'Unable to change password because you are not authenticated.',
                'not_found' => 'Unable to change password because the user account could not be found.',
                'server' => 'Unable to change password. Please try again later.',
            ]);
        }
    }

    public function changeEmail(Request $request)
    {
        try {
            $user = $this->authenticatedUser($request);

            $validated = $request->validate([
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required'],
            ]);

            $user = $this->service->changeEmail(
                $user,
                $validated
            );

            return response()->json([
                'message' => 'Email changed successfully.',
                'user' => $user,
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, [
                'validation' => 'Change email request validation failed.',
                'authentication' => 'Unable to change email because you are not authenticated.',
                'not_found' => 'Unable to change email because the user account could not be found.',
                'server' => 'Unable to change email. Please try again later.',
            ]);
        }
    }

    public function sendOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => [
                    'required',
                    'email',
                ],
            ]);
            $this->service->sendOtp($validated['email']);

            return response()->json([
                'message' => 'OTP berhasil dikirim ke email'
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, [
                'validation' => 'Send OTP request validation failed.',
                'not_found' => 'Unable to send OTP because the email is not registered.',
                'server' => 'Unable to send OTP. Please try again later.',
            ]);
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => [
                    'required',
                    'email',
                ],
                'otp' => ['required', 'string'],
            ]);

            $this->service->verifyOtp(
                $validated['email'],
                $validated['otp']
            );

            return response()->json([
                'message' => 'OTP valid'
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, [
                'validation' => 'Verify OTP request validation failed.',
                'not_found' => 'Unable to verify OTP because the email is not registered.',
                'server' => 'Unable to verify OTP. Please try again later.',
            ]);
        }
    }

    public function createNewPassword(
        Request $request
    ) {
        try {
            $validated = $request->validate([
                'email' => [
                    'required',
                    'email',
                ],
                'otp' => ['required', 'string'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'password_confirmation' => ['required', 'same:password'],
            ]);
            $this->service
                ->createNewPassword(
                    $validated['email'],
                    $validated['otp'],
                    $validated['password']
                );

            return response()->json([
                'message' => 'Password berhasil diubah'
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, [
                'validation' => 'Reset password request validation failed.',
                'not_found' => 'Unable to reset password because the email is not registered.',
                'server' => 'Unable to reset password. Please try again later.',
            ]);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $this->authenticatedUser($request);

            $this->service->logout($user);

            return response()->json([
                'status'  => 'success',
                'message' => 'Successfully logged out',
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse($e, [
                'authentication' => 'Unable to logout because you are not authenticated.',
                'server' => 'Unable to logout. Please try again later.',
            ]);
        }
    }

    private function authenticatedUser(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        return $user;
    }

    private function errorResponse(Throwable $e, array $messages = [])
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                'status'  => 'error',
                'message' => $messages['validation'] ?? 'The given data was invalid.',
                'errors'  => $e->errors(),
            ], 422);
        }

        if ($e instanceof AuthenticationException) {
            return response()->json([
                'status'  => 'error',
                'message' => $messages['authentication'] ?? ($e->getMessage() ?: 'Unauthenticated.'),
            ], 401);
        }

        if ($e instanceof HttpException) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        }

        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'status'  => 'error',
                'message' => $messages['not_found'] ?? 'Resource not found.',
            ], 404);
        }

        return response()->json([
            'status'  => 'error',
            'message' => $messages['server'] ?? 'Internal server error.',
        ], 500);
    }
}
