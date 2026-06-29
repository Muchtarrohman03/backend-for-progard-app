<?php

namespace App\Jobs;

use App\Models\Position;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class CleanExpiredPositionsJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $deleted = Position::query()
            ->where('created_at', '<', now()->startOfDay())
            ->delete();
        if ($deleted === 0) {
            Log::info('No expired positions found.');
            return;
        }
        Log::info('Daily position cleanup completed.', [
            'deleted_rows' => $deleted,
            'executed_at' => now(),
        ]);
    }
    public function failed(Throwable $exception): void
    {
        Log::error('Clean positions failed.', [
            'message' => $exception->getMessage(),
        ]);
    }
}
