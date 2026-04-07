<?php

namespace App\Listeners;

use App\Events\AbsenceCreated;
use App\Models\User;
use App\Services\FirebaseService;

class SendAbsenceForApprovalNotification
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
    public function handle(AbsenceCreated $event): void
    {
        $absence = $event->absence;
        $employee   = $absence->employee;

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
}
