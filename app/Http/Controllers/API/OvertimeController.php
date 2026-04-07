<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Overtime\ApprovalOvertimeRequest;
use App\Http\Requests\Overtime\GetOvertimeByDateRequest;
use App\Http\Requests\Overtime\GetOvertimeByDivisionAndDateRequest;
use App\Http\Requests\Overtime\StoreOvertimeRequest;
use App\Services\OvertimeService;

class OvertimeController extends Controller
{

    public function __construct(
        private OvertimeService $service
    ) {}

    public function index()
    {
        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'List retrieved',
            'data' => $this->service->getMyOvertimes()
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Detail retrieved',
            'data' => $this->service->getById($id)
        ]);
    }

    public function store(StoreOvertimeRequest $request)
    {
        $overtime = $this->service->store($request);

        return response()->json([
            'response_code' => 201,
            'status' => 'success',
            'message' => 'Created successfully',
            'data' => $overtime
        ], 201);
    }

    public function getMyOvertimesByDate(GetOvertimeByDateRequest $request)

    {
        $data = $this->service->getByDate($request->date);
        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => $data->isEmpty()
                ? 'No overtimes found for the specified date'
                : 'Overtimes retrieved successfully',
            'data' => $data
        ], 200);
    }
    public function getOvertimesByDivisionAndDate(GetOvertimeByDivisionAndDateRequest $request)
    {
        $data = $this->service->getByDivisionAndDate(
            $request->division,
            $request->date
        );

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => $data->isEmpty()
                ? 'No overtimes found for the specified division and date'
                : 'Overtimes retrieved successfully',
            'data' => $data
        ], 200);
    }
    public function spvSelectOvertime()
    {
        $data = $this->service->spvSelectToday();
        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => $data->isEmpty()
                ? 'No overtimes found for today'
                : 'Overtimes retrieved successfully',
            'data' => $data
        ]);
    }

    public function spvAprovalOvertime(
        ApprovalOvertimeRequest $request,
        $id
    ) {
        $overtime = $this->service->approvalSpv(
            $id,
            $request->status
        );

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Updated successfully',
            'data' => $overtime
        ]);
    }
    public function siteManagerSelectOvertime()
    {
        $data = $this->service->siteManagerSelectToday();
        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => $data->isEmpty()
                ? 'Tidak ada overtimes untuk hari ini'
                : 'Berhasil mengambil overtimes untuk hari ini',
            'data' => $data
        ]);
    }

    public function siteManagerApprovalOvertime(
        ApprovalOvertimeRequest $request,
        $id
    ) {
        $overtime = $this->service->approvalSiteManager(
            $id,
            $request->status
        );

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Status updated successfully',
            'data' => $overtime
        ]);
    }

    public function destroy($id)
    {
        $this->service->delete($id);

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Deleted successfully'
        ]);
    }
}
