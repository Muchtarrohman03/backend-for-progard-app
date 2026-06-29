<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;


class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $service
    ) {}

    public function statOverview()
    {
        return response()->json(
            $this->service->statOverview()
        );
    }

    public function divisionOverview()
    {
        return response()->json(
            $this->service->divisionOverview()
        );
    }
    public function allOverview()
    {
        return response()->json(
            $this->service->allOverview()
        );
    }

    public function weeklySubmissionChart()
    {
        return response()->json(
            $this->service->weeklySubmissionChart()
        );
    }

    public function gardenerRanking()
    {
        return response()->json(
            $this->service->gardenerRanking()
        );
    }
    public function topPerformers()
    {
        return response()->json(
            $this->service->allRanking()
        );
    }
}
