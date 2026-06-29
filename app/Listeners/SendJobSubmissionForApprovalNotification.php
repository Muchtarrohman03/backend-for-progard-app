<?php

namespace App\Listeners;

use App\Events\JobSubmissionCreated;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Contracts\Queue\ShouldQueue; // ← tambah ini
use Illuminate\Queue\InteractsWithQueue;    // ← tambah ini
use Illuminate\Support\Facades\Log;

class SendJobSubmissionForApprovalNotification implements ShouldQueue // ← implement ini
{
    use InteractsWithQueue; // ← tambah ini

    public int $tries = 3;         // retry 3x jika gagal
    public int $backoff = 5;       // tunggu 5 detik antar retry
    public int $timeout = 30;      // timeout per attempt

    public function __construct(
        protected FirebaseService $firebaseService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(JobSubmissionCreated $event): void
    {
        $submission = $event->jobSubmission;
        $employee   = $submission->employee;

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
                '1 Pekerjaan Baru Perlu Divalidasi',
                "{$employee->name} mengajukan pekerjaan baru",
                'supervisor_approval_job_submission',
            ],
            'site_manager' => [
                'Pekerjaan Perlu Validasi Akhir',
                "{$employee->name} mengajukan pekerjaan untuk validasi",
                'site_manager_approval_job_submission',
            ],
        };

        $this->firebaseService->sendNotification(
            fcmToken: $targetUser->fcm_token,
            title: $title,
            body: $body,
            data: [
                'submission_id' => (string) $submission->id,
                'screen' => $screen,
            ]
        );
    }
    // Optional: handle jika semua retry gagal
    public function failed(JobSubmissionCreated $event, \Throwable $exception): void
    {
        Log::error('FCM notification failed after retries', [
            'submission_id' => $event->jobSubmission->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
