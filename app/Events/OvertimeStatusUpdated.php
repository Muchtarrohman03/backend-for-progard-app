<?php

namespace App\Events;

use App\Models\Overtime;
use Illuminate\Foundation\Events\Dispatchable;

class OvertimeStatusUpdated
{
    use Dispatchable;

    // ✅ Event membawa data Overtime yang statusnya berubah
    public function __construct(
        public Overtime $overtime
    ) {}
}
