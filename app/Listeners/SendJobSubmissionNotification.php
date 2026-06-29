<?php

namespace App\Listeners;

use App\Events\JobSubmissionStatusUpdated;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue; // ← tambah
use Illuminate\Queue\InteractsWithQueue;    // ← tambah
use Illuminate\Support\Facades\Log;

class SendJobSubmissionNotification implements ShouldQueue // ← tambah
{
    use InteractsWithQueue; // ← tambah

    public int $tries = 3;
    public int $backoff = 5;
    public int $timeout = 30;

    public function __construct(
        protected FirebaseService $firebaseService
    ) {}

    public function handle(JobSubmissionStatusUpdated $event): void
    {
        $submission = $event->jobSubmission;

        $submission->load([
            'employee.roles',
            'approver.roles',
            'approver.profile',
        ]);

        $employee = $submission->employee;

        if (!$employee || !$employee->fcm_token) {
            return;
        }

        $submittedAt = Carbon::parse($submission->submitted_at)
            ->locale('id')
            ->translatedFormat('d F Y, H:i');

        $approver = $submission->approver;

        $approverName = $approver?->name ?? 'System';

        $approverRole = $approver
            ?->getRoleNames()
            ->first() ?? 'Admin';

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

    public function failed(JobSubmissionStatusUpdated $event, \Throwable $exception): void
    {
        Log::error('FCM status notification failed', [
            'submission_id' => $event->jobSubmission->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
