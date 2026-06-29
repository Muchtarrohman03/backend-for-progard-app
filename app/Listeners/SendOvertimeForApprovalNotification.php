<?php

namespace App\Listeners;

use App\Events\OvertimeCreated;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue; // ← tambah ini
use Illuminate\Queue\InteractsWithQueue;    // ← tambah ini
use Illuminate\Support\Facades\Log;

class SendOvertimeForApprovalNotification implements ShouldQueue // ← implement ini
{
    use InteractsWithQueue; // ← tambah ini

    public int $tries = 3;         // retry 3x jika gagal
    public int $backoff = 5;       // tunggu 5 detik antar retry
    public int $timeout = 30;      // timeout per attempt

    /**
     * Create the event listener.
     */
    public function __construct(
        protected FirebaseService $firebaseService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(OvertimeCreated $event): void
    {
        $overtime = $event->overtime;
        $employee = $overtime->employee;

        if (!$employee) return;

        $targetRole = match (true) {
            $employee->hasRole('gardener', 'api') => 'supervisor',
            ($employee->hasRole('supervisor', 'api') || $employee->hasRole('staff', 'api')) => 'site_manager',
            default => null,
        };

        if (!$targetRole) return;

        // ✅ Hanya satu query, di dalam try-catch
        try {

            $targetUser = User::role($targetRole, 'api')

                ->when(
                    $targetRole === 'supervisor',

                    function ($query) use ($employee) {

                        $query->whereHas(
                            'profile',

                            function ($q) use ($employee) {

                                $q->where(
                                    'division_id',
                                    $employee->division_id
                                );
                            }
                        );
                    }
                )

                ->whereNotNull('fcm_token')

                ->first();
        } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {

            Log::warning(
                "Role '{$targetRole}' tidak ditemukan",
                ['guard' => 'api']
            );

            return;
        }

        if (!$targetUser) return;
        if ($targetUser->id === $employee->id) return;

        [$title, $body, $screen] = match ($targetRole) {
            'supervisor' => [
                '1 Lemburan Baru Perlu Divalidasi',
                "{$employee->name} mengajukan lembur baru",
                'supervisor_approval_overtime',
            ],
            'site_manager' => [
                'Lemburan Perlu Validasi Akhir',
                "{$employee->name} mengajukan lembur untuk validasi",
                'site_manager_approval_overtime',
            ],
        };

        $this->firebaseService->sendNotification(
            fcmToken: $targetUser->fcm_token,
            title: $title,
            body: $body,
            data: [
                'overtime_id' => (string) $overtime->id,
                'screen' => $screen,
            ]
        );
    }
    // Optional: handle jika semua retry gagal
    public function failed(OvertimeCreated $event, \Throwable $exception): void
    {
        Log::error('FCM notification failed after retries', [
            'overtime_id' => $event->overtime->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
