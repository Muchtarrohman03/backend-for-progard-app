<?php

namespace App\Listeners;

use App\Events\AbsenceStatusUpdated;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue; // ← tambah ini
use Illuminate\Queue\InteractsWithQueue;    // ← tambah ini
use Illuminate\Support\Facades\Log;

class SendAbsenceNotification implements ShouldQueue
{
    use InteractsWithQueue;
    public int $tries = 3;         // retry 3x jika gagal
    public int $backoff = 5;       // tunggu 5 detik antar retry
    public int $timeout = 30;      // timeout per attempt

    public function __construct(
        protected FirebaseService $firebaseService
    ) {}

    public function handle(AbsenceStatusUpdated $event): void
    {
        $absence = $event->absence;

        $absence->load([
            'employee.roles',
            'approver.roles',
            'approver.profile',
        ]);

        $employee = $absence->employee;

        if (!$employee || !$employee->fcm_token) {
            return;
        }

        $submittedAt = Carbon::parse($absence->submitted_at)
            ->locale('id')
            ->translatedFormat('d F Y, H:i');

        $approver = $absence->approver;

        $approverName = $approver?->name ?? 'System';

        $approverRole = $approver
            ?->getRoleNames()
            ->first() ?? 'Admin';

        [$title, $body] = match ($absence->status) {

            'approved' => [
                '✅ Laporan Izin yang Kamu Buat Telah Disetujui',

                "Diajukan pada: {$submittedAt}\nDisetujui oleh: {$approverName} ({$approverRole})",
            ],

            'rejected' => [
                '❌ Laporan Izin yang Kamu Buat Ditolak',

                "Diajukan pada: {$submittedAt}\nDitolak oleh: {$approverName} ({$approverRole})",
            ],

            default => [
                'Status Izin',
                "Status izin kamu telah diperbarui",
            ],
        };

        $this->firebaseService->sendNotification(
            fcmToken: $employee->fcm_token,

            title: $title,

            body: $body,

            data: [
                'absence_id' => (string) $absence->id,
                'status'        => $absence->status,
                'screen'        => 'absence_submission',
            ]
        );
    }
    public function failed(AbsenceStatusUpdated $event, \Throwable $exception): void
    {
        Log::error('FCM status notification failed', [
            'absence_id' => $event->absence->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
