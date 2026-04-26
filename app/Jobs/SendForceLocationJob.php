<?php
// app/Jobs/SendForceLocationJob.php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class SendForceLocationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        private readonly array $userIds,
        private readonly int   $actorId
    ) {}

    public function handle(Messaging $messaging): void
    {
        collect($this->userIds)
            ->chunk(100)
            ->each(function ($chunk) use ($messaging) {
                $users = User::whereIn('id', $chunk)
                    ->whereNotNull('fcm_token')
                    ->get();

                foreach ($users as $user) {
                    $message = CloudMessage::withTarget('token', $user->fcm_token)
                        ->withNotification(
                            Notification::create(
                                title: 'Permintaan Lokasi',
                                body: 'Supervisor meminta data lokasi Anda sekarang.'
                            )
                        )
                        ->withData([
                            'type'      => 'FORCE_LOCATION_UPDATE',
                            'actor_id'  => (string) $this->actorId,
                            'timestamp' => now()->toISOString(),
                        ])
                        ->withAndroidConfig([
                            'priority' => 'high',
                            'notification' => [
                                'channel_id' => 'force_location',
                            ],
                        ]);

                    try {
                        $messaging->send($message);
                    } catch (\Throwable $e) {
                        // Token tidak valid → hapus dari DB agar tidak dikirim lagi
                        if (
                            str_contains($e->getMessage(), 'NOT_FOUND') ||
                            str_contains($e->getMessage(), 'INVALID_ARGUMENT')
                        ) {
                            $user->update(['fcm_token' => null]);
                        }

                        Log::warning("FCM failed for user {$user->id}: " . $e->getMessage());
                    }
                }
            });
    }
}
