<?php

namespace App\Events;

use App\Models\Absence;

use Illuminate\Foundation\Events\Dispatchable;

class AbsenceStatusUpdated
{
    use Dispatchable;

    // ✅ Event membawa data Absence yang statusnya berubah
    public function __construct(
        public Absence $absence
    ) {}
}
