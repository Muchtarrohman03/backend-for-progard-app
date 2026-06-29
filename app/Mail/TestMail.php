<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class TestMail extends Mailable
{
    public function build()
    {
        return $this
            ->subject('Test Email Mailtrap')
            ->html('Mailtrap berhasil terhubung ke Laravel.');
    }
}
