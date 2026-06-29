<?php

namespace App\Console\Commands;

use App\Services\PositionService;
use Illuminate\Console\Command;

class CleanPositionsCommand extends Command
{
    protected $signature = 'positions:clean';

    protected $description = 'Dispatch daily clean positions job';

    public function handle(PositionService $positionService): int
    {
        $positionService->cleanExpiredPositions();

        $this->info('Clean positions job dispatched.');

        return self::SUCCESS;
    }
}
