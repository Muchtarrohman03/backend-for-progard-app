<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AbsenceService;
use App\Http\Requests\Absence\AbsenceStoreRequest;
use App\Http\Requests\Absence\AbsenceUpdateStatusRequest;
use App\Http\Requests\Absence\AbsenceFilterByDateRequest;
use App\Http\Requests\Absence\AbsenceFilterByDateAndDivisionRequest;
use Illuminate\Http\JsonResponse;

class AbsenceController extends Controller
{
    public function __construct(
        private readonly AbsenceService $service
    ) {}

    /*
    |--------------------------------------------------------------------------
    | General
    |--------------------------------------------------------------------------
    */

    public function index(): JsonResponse
    {
        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'List of absences retrieved successfully',
            'data' => $this->service->getMyAbsences(),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Absence retrieved successfully',
            'data' => $this->service->getMyAbsenceById((int) $id),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Employee
    |--------------------------------------------------------------------------
    */

    public function store(AbsenceStoreRequest $request): JsonResponse
    {
        return response()->json([
            'response_code' => 201,
            'status' => 'success',
            'message' => 'Absence created successfully',
            'data' => $this->service->store($request->validated()),
        ], 201);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->service->destroy((int) $id);

        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Absence deleted successfully',
        ]);
    }

    public function getMyAbsencesByDate(
        AbsenceFilterByDateRequest $request
    ): JsonResponse {
        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Absences retrieved successfully',
            'data' => $this->service->getMyAbsencesByDate(
                $request->validated()['date']
            ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Supervisor
    |--------------------------------------------------------------------------
    */

    public function spvSelectAbsences(): JsonResponse
    {
        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Absences retrieved successfully',
            'data' => $this->service->spvSelectGardenerAbsencesToday(),
        ]);
    }

    public function spvApprovalAbsence(
        AbsenceUpdateStatusRequest $request,
        string $id
    ): JsonResponse {
        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Absence status updated successfully',
            'data' => $this->service
                ->spvApproveGardenerAbsence(
                    (int) $id,
                    $request->validated()['status'],
                    $request->validated()['comment']
                ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Site Manager
    |--------------------------------------------------------------------------
    */

    public function siteManagerSelectAbsences(): JsonResponse
    {
        $data = $this->service->siteManagerSelectSpvAndStaffAbsencesToday();
        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => $data->isEmpty()
                ? 'Tidak ada absences untuk hari ini'
                : 'Berhasil mengambil absences untuk hari ini',
            'data' => $data,
        ]);
    }

    public function siteManagerApprovalAbsence(
        AbsenceUpdateStatusRequest $request,
        string $id
    ): JsonResponse {
        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Absence status updated successfully',
            'data' => $this->service
                ->siteManagerApproveSpvAndStaffAbsence(
                    (int) $id,
                    $request->validated()['status'],
                    $request->validated()['comment']
                ),
        ]);
    }


    public function getAbsencesByDivisionAndDate(
        AbsenceFilterByDateAndDivisionRequest $request
    ): JsonResponse {
        $data = $this->service->getAbsencesByDivisionAndDate(
            $request->division,
            $request->date
        );
        return response()->json([
            'response_code' => 200,
            'status' => 'success',
            'message' => 'Absences retrieved successfully',
            'data' => $data->isEmpty()
                ? 'No absences found for the specified division and date'
                : $data
        ]);
    }
}
