<?php

namespace App\Listeners;

use App\Events\OvertimeStatusUpdated;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue; // ← tambah ini
use Illuminate\Queue\InteractsWithQueue;    // ← tambah ini
use Illuminate\Support\Facades\Log;

class SendOvertimeNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 5;
    public int $timeout = 30;
    // ✅ Hapus: protected $overtime; — tidak diperlukan

    public function __construct(
        protected FirebaseService $firebaseService
    ) {}

    public function handle(OvertimeStatusUpdated $event): void
    {
        $overtime = $event->overtime;

        $overtime->load([
            'employee.roles',
            'approver.roles',
            'approver.profile',
        ]);

        $employee = $overtime->employee;

        if (!$employee || !$employee->fcm_token) {
            return;
        }

        $submittedAt = Carbon::parse($overtime->submitted_at)
            ->locale('id')
            ->translatedFormat('d F Y, H:i');

        $approver = $overtime->approver;

        $approverName = $approver?->name ?? 'System';

        $approverRole = $approver
            ?->getRoleNames()
            ->first() ?? 'Admin';

        [$title, $body] = match ($overtime->status) {

            'approved' => [
                '✅ Laporan Lembur Yang Kamu Buat Telah Disetujui',

                "Diajukan pada: {$submittedAt}\nDisetujui oleh: {$approverName} ({$approverRole})",
            ],

            'rejected' => [
                '❌ Laporan Lembur  Yang Kamu Buat Ditolak',

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
                'overtime_id' => (string) $overtime->id,
                'status'      => $overtime->status,
                'screen'      => 'overtime_submission',
            ]
        );
    }

    public function failed(OvertimeStatusUpdated $event, \Throwable $exception): void
    {
        Log::error('FCM overtime status notification failed', [
            'overtime_id' => $event->overtime->id,
            'error'       => $exception->getMessage(),
        ]);
    }
}
