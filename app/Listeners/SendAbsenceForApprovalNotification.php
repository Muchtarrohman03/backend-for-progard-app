<?php

namespace App\Listeners;

use App\Events\AbsenceCreated;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue; // ← tambah ini
use Illuminate\Queue\InteractsWithQueue;    // ← tambah ini
use Illuminate\Support\Facades\Log;

class SendAbsenceForApprovalNotification implements ShouldQueue
{
    use InteractsWithQueue;
    public int $tries = 3;         // retry 3x jika gagal
    public int $backoff = 5;       // tunggu 5 detik antar
    public int $timeout = 30;      // timeout per attempt


    public function __construct(
        protected FirebaseService $firebaseService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(AbsenceCreated $event): void
    {
        $absence = $event->absence;
        $employee   = $absence->employee;

        if (!$employee) return;
        // ✅ Guard 'api' karena semua role mobile pakai guard api
        $targetRole = match (true) {
            $employee->hasRole('gardener', 'api') => 'supervisor',
            ($employee->hasRole('supervisor', 'api') || $employee->hasRole('staff', 'api')) => 'site_manager',
            default => null,
        };

        if (!$targetRole) return;

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
                '1 Izin Baru Perlu Divalidasi',
                "{$employee->name} mengajukan izin baru",
                'supervisor_approval_absence',
            ],
            'site_manager' => [
                'Izin Perlu Validasi Akhir',
                "{$employee->name} mengajukan izin untuk validasi",
                'site_manager_approval_absence',
            ],
        };

        // ✅ Kirim notifikasi
        $this->firebaseService->sendNotification(
            fcmToken: $targetUser->fcm_token,
            title: $title,
            body: $body,
            data: [
                'submission_id' => (string) $absence->id,
                'screen' => $screen,
            ]
        );
    }
    public function failed(AbsenceCreated $event, \Throwable $exception): void
    {
        Log::error('Failed to send absence approval notification', [
            'absence_id' => $event->absence->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
