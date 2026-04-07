<?php

namespace App\Events;

use App\Models\JobSubmission;
use Illuminate\Foundation\Events\Dispatchable;

class JobSubmissionStatusUpdated
{
    use Dispatchable;

    // ✅ Event membawa data JobSubmission yang statusnya berubah
    public function __construct(
        public JobSubmission $jobSubmission
    ) {}
}
