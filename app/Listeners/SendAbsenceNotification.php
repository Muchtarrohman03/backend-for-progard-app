<?php

namespace App\Listeners;

use App\Events\AbsenceStatusUpdated;
use App\Services\FirebaseService;
use Carbon\Carbon;

class SendabsenceNotification
{
    public function __construct(
        protected FirebaseService $firebaseService
    ) {}

    public function handle(AbsenceStatusUpdated $event): void
    {
        $absence = $event->absence;
        $employee   = $absence->employee;

        if (!$employee || !$employee->fcm_token) {
            return;
        }

        $submittedAt = Carbon::parse($absence->submitted_at)
            ->locale('id')
            ->translatedFormat('d F Y, H:i');

        $approver = $absence->approver;

        $approverName = $approver?->name ?? 'System';
        $approverRole = $approver?->getRoleNames()->first() ?? 'Unknown';

        [$title, $body] = match ($absence->status) {
            'approved' => [
                '✅ Izin Yang Kamu Buat Telah Disetujui',
                "Diajukan pada: {$submittedAt}\nDisetujui oleh: {$approverName} ({$approverRole})",
            ],
            'rejected' => [
                '❌ Izin Yang Kamu Buat Ditolak',
                "Diajukan pada: {$submittedAt}\nDitolak oleh: {$approverName} ({$approverRole})",
            ],
            default => [
                'Status Pekerjaan',
                "Status Izin kamu masih menunggu persetujuan",
            ],
        };

        $this->firebaseService->sendNotification(
            fcmToken: $employee->fcm_token,
            title: $title,
            body: $body,
            data: [
                'absence_id' => (string) $absence->id,
                'status'        => $absence->status,
                'screen'        => 'absence',
            ]
        );
    }
}
