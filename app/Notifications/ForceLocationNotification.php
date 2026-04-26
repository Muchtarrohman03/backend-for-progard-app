<?php
// app/Listeners/SendForceLocationNotification.php

namespace App\Listeners;

use App\Events\ForceLocationUpdateRequested;
use App\Jobs\SendForceLocationJob;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;

class ForceLocationNotification implements ShouldQueue
{
    public function handle(ForceLocationUpdateRequested $event): void
    {
        $actor    = $event->actor;
        $scope    = $event->scope;
        $division = $event->division;

        $targetRoles = $scope === 'global'
            ? ['gardener', 'staff', 'supervisor']
            : ['gardener'];

        $query = User::role($targetRoles)->whereNotNull('fcm_token');

        if ($scope === 'division' && $division) {
            $query->where('division', $division);
        }

        $targets = $query->get();

        if ($targets->isEmpty()) {
            return;
        }

        foreach ($targets as $user) {
            Cache::put(
                key: "force_location:{$user->id}",
                value: [
                    'requested_by' => $actor->id,
                    'scope'        => $scope,
                    'division'     => $division,
                    'requested_at' => now()->toISOString(),
                ],
                ttl: now()->addMinutes(5)
            );
        }

        // Tidak pakai Notification::send() — langsung dispatch Job
        SendForceLocationJob::dispatch(
            $targets->pluck('id')->toArray(),
            $actor->id
        );
    }
}
