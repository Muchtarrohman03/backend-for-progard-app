<?php

namespace App\Listeners;

use App\Events\JobSubmissionCreated;
use App\Models\User;
use App\Services\FirebaseService;

class SendJobSubmissionForApprovalNotification
{
    /**
     * Create the event listener.
     */
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

        // 🔥 Tentukan target role
        $targetRole = match (true) {
            $employee->hasRole('gardener') => 'supervisor',
            ($employee->hasRole('supervisor') || $employee->hasRole('staff')) => 'site_manager',
            default => null,
        };

        if (!$targetRole) return;

        // 🔥 Ambil user tujuan (1 per division)
        $targetUser = User::role($targetRole)
            ->when($targetRole === 'supervisor', function ($query) use ($employee) {
                // ✅ hanya supervisor yang pakai division
                $query->where('division', $employee->division);
            })
            ->whereNotNull('fcm_token')
            ->first();

        if (!$targetUser) return;

        // ❌ Hindari kirim ke diri sendiri
        if ($targetUser->id === $employee->id) return;

        // 🔥 Custom pesan berdasarkan role
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

        // ✅ Kirim notifikasi
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
}
