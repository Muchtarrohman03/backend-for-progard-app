<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;

class ForgotPasswordOtpMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $otp
    ) {}

    public function build()
    {
        return $this
            ->subject('Kode OTP Reset Password')
            ->view('emails.forgot-password-otp');
    }
}
