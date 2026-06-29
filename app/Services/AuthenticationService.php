<?php

namespace App\Services;

use App\Models\User;
use App\Models\PasswordOtp;
use App\Mail\ForgotPasswordOtpMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class AuthenticationService
{
    public function register(array $data)
    {
        if (User::where('email', $data['email'])->exists()) {
            throw new HttpException(409, 'Registration failed because the email is already registered.');
        }

        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }



    public function login(array $credentials)
    {
        if (!Auth::attempt($credentials)) {
            throw new HttpException(
                401,
                'Login failed. The email or password is incorrect.'
            );
        }

        $user = Auth::user();

        if (!$user) {
            throw new HttpException(401, 'Login failed because the authenticated user could not be loaded.');
        }

        $hasApiGuard = $user->roles()->where('guard_name', 'api')->exists();

        if (!$hasApiGuard) {
            Auth::logout();

            throw new HttpException(403, 'Login failed. Your account is not allowed to access this application.');
        }

        $division = $user->division;
        $token = $user->createToken($credentials['device_name'] ?? 'mobile')->plainTextToken;

        return [
            'user'     => $user,
            'token'    => $token,
            'division' => $division,
        ];
    }
    public function changePassword(User $user, array $data)
    {
        // Cek password lama
        if (!Hash::check($data['current_password'], $user->password)) {
            throw new HttpException(
                400,
                'Password change failed. Current password is incorrect.'
            );
        }

        // Mencegah password baru sama dengan password lama
        if (Hash::check($data['new_password'], $user->password)) {
            throw new HttpException(
                400,
                'Password change failed. New password cannot be the same as the current password.'
            );
        }

        // Update password
        $user->update([
            'password' => Hash::make($data['new_password']),
        ]);

        // Optional: logout dari semua device
        $user->tokens()->delete();

        return true;
    }

    public function changeEmail(User $user, array $data)
    {
        // Verifikasi password
        if (!Hash::check($data['password'], $user->password)) {
            throw new HttpException(400, 'Email change failed. Password is incorrect.');
        }

        // Cek apakah email baru sama dengan email lama
        if ($data['email'] === $user->email) {
            throw new HttpException(400, 'Email change failed. The new email must be different from the current email.');
        }

        // Update email
        $user->update([
            'email' => $data['email'],
        ]);

        return $user;
    }

    public function sendOtp(string $email): void
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw new HttpException(404, 'OTP request failed. The email is not registered.');
        }

        // hapus OTP lama
        PasswordOtp::where('user_id', $user->id)->delete();

        $otp = (string) random_int(100000, 999999);

        PasswordOtp::create([
            'user_id' => $user->id,
            'otp' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
            'used' => false,
        ]);

        try {
            Mail::to($user->email)
                ->queue(new ForgotPasswordOtpMail($otp));
        } catch (Throwable $e) {
            throw new HttpException(500, 'OTP request failed. Unable to send OTP email.', $e);
        }
    }

    public function verifyOtp(
        string $email,
        string $otp
    ): void {

        $user = User::where('email', $email)
            ->first();

        if (!$user) {
            throw new HttpException(404, 'OTP verification failed. The email is not registered.');
        }

        $passwordOtp = PasswordOtp::where('user_id', $user->id)
            ->where('used', false)
            ->latest()
            ->first();

        if (!$passwordOtp) {
            throw new HttpException(400, 'OTP verification failed. No active OTP was found for this email.');
        }

        if ($passwordOtp->expires_at->isPast()) {
            throw new HttpException(400, 'OTP verification failed. The OTP has expired.');
        }

        if (!Hash::check($otp, $passwordOtp->otp)) {
            throw new HttpException(400, 'OTP verification failed. The OTP code is incorrect.');
        }
    }

    public function createNewPassword(
        string $email,
        string $otp,
        string $newPassword
    ): void {

        $user = User::where('email', $email)
            ->first();

        if (!$user) {
            throw new HttpException(404, 'Password reset failed. The email is not registered.');
        }

        $passwordOtp = PasswordOtp::where('user_id', $user->id)
            ->where('used', false)
            ->latest()
            ->first();

        if (!$passwordOtp) {
            throw new HttpException(400, 'Password reset failed. No active OTP was found for this email.');
        }

        if ($passwordOtp->expires_at->isPast()) {
            throw new HttpException(400, 'Password reset failed. The OTP has expired.');
        }

        if (!Hash::check($otp, $passwordOtp->otp)) {
            throw new HttpException(400, 'Password reset failed. The OTP code is incorrect.');
        }

        if (Hash::check($newPassword, $user->password)) {
            throw new HttpException(400, 'Password reset failed. New password cannot be the same as the current password.');
        }

        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        $passwordOtp->update([
            'used' => true
        ]);
    }

    public function logout($user)
    {
        if (!$user) {
            throw new HttpException(401, 'Logout failed because the user is not authenticated.');
        }

        $user->tokens()->delete();
    }
}
