<?php
// app/Listeners/SendForceLocationNotification.php

namespace App\Listeners;

use App\Events\ForceLocationUpdateRequested;
use App\Jobs\SendForceLocationJob;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendForceLocationNotification implements ShouldQueue
{
    // Sesuaikan dengan hasil tinker di atas: 'sanctum' atau 'api'
    private const GUARD = 'api';

    public function handle(ForceLocationUpdateRequested $event): void
    {
        $actor    = $event->actor;
        $scope    = $event->scope;
        $division = $event->division;

        $targetRoles = $scope === 'global'
            ? ['gardener', 'staff', 'supervisor']
            : ['gardener'];

        // ✅ Tambahkan guard name sebagai parameter kedua
        $query = User::role($targetRoles, self::GUARD)
            ->whereNotNull('fcm_token');

        if ($scope === 'division' && $division) {
            $query->where('division', $division);
        }

        $targets = $query->get();

        if ($targets->isEmpty()) {
            Log::info('ForceLocation: no targets found', [
                'scope'    => $scope,
                'division' => $division,
                'roles'    => $targetRoles,
            ]);
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

        Log::info('ForceLocation: dispatching job', [
            'targets' => $targets->pluck('id'),
        ]);

        SendForceLocationJob::dispatch(
            $targets->pluck('id')->toArray(),
            $actor->id
        );
    }
}
