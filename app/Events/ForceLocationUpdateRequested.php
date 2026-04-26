<?php
// app/Events/ForceLocationUpdateRequested.php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ForceLocationUpdateRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $actor,
        public readonly string $scope,       // 'global' | 'division'
        public readonly ?string $division = null
    ) {}
}
