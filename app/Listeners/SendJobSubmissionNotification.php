<?php

namespace App\Listeners;

use App\Events\JobSubmissionStatusUpdated;
use App\Services\FirebaseService;
use Carbon\Carbon;

class SendJobSubmissionNotification
{
    public function __construct(
        protected FirebaseService $firebaseService
    ) {}

    public function handle(JobSubmissionStatusUpdated $event): void
    {
        $submission = $event->jobSubmission;
        $employee   = $submission->employee;

        if (!$employee || !$employee->fcm_token) {
            return;
        }

        $submittedAt = Carbon::parse($submission->submitted_at)
            ->locale('id')
            ->translatedFormat('d F Y, H:i');

        $approver = $submission->approver;

        $approverName = $approver?->name ?? 'System';
        $approverRole = $approver?->getRoleNames()->first() ?? 'Unknown';

        [$title, $body] = match ($submission->status) {
            'approved' => [
                '✅ Laporan Pekerjaan Yang Kamu Buat Telah Disetujui',
                "Diajukan pada: {$submittedAt}\nDisetujui oleh: {$approverName} ({$approverRole})",
            ],
            'rejected' => [
                '❌ Laporan Pekerjaan Yang Kamu Buat Ditolak',
                "Diajukan pada: {$submittedAt}\nDitolak oleh: {$approverName} ({$approverRole})",
            ],
            default => [
                'Status Pekerjaan',
                "Status pekerjaan kamu telah diperbarui",
            ],
        };

        $this->firebaseService->sendNotification(
            fcmToken: $employee->fcm_token,
            title: $title,
            body: $body,
            data: [
                'submission_id' => (string) $submission->id,
                'status'        => $submission->status,
                'screen'        => 'job_submission',
            ]
        );
    }
}
