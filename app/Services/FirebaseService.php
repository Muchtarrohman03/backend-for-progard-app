<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Support\Facades\Log;



class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(
            base_path(config('firebase.projects.app.credentials'))
        );
        $this->messaging = $factory->createMessaging();
    }


    public function sendNotification(string $fcmToken, string $title, string $body, array $data = []): void
    {
        if (empty($fcmToken)) return;

        $payload = [
            'token' => $fcmToken,

            // ✅ INI YANG BIKIN MUNCUL DI BACKGROUND
            'notification' => [
                'title' => $title,
                'body'  => $body,
            ],

            // ✅ data tetap dipakai untuk Flutter
            'data' => array_merge([
                'title' => $title,
                'body'  => $body,
            ], $data),

            // ✅ WAJIB untuk Android
            'android' => [
                'priority' => 'high',
            ],
        ];

        Log::info('FCM Payload:', $payload);

        $message = CloudMessage::fromArray($payload);
        $this->messaging->send($message);
    }
}
