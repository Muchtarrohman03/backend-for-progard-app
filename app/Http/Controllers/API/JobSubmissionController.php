<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobSubmission\GetJobSubmissionByDateRequest;
use App\Http\Requests\JobSubmission\GetJobSubmissionByDivisionAndDateRequest;
use App\Http\Requests\JobSubmission\JobSubmissionStoreRequest;
use App\Http\Requests\JobSubmission\SpvApprovalJobSubmissionRequest;
use App\Models\JobSubmission;
use App\Services\JobSubmissionService;

class JobSubmissionController extends Controller
{
    public function __construct(
        private JobSubmissionService $service
    ) {}

    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->service->getAll()
        ]);
    }

    public function getMySubmissionByDate(GetJobSubmissionByDateRequest $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->service->getByDate($request->date)

        ]);
    }

    public function getByDivisionAndDate(GetJobSubmissionByDivisionAndDateRequest $request)
    {
        $data = $this->service->getByDivisionAndDate($request->division, $request->date);
        return response()->json([
            'status' => 'success',
            'data' => $data->isEmpty()
                ? 'No submissions found for the specified division and date'
                : $data
        ]);
    }

    public function store(JobSubmissionStoreRequest $request)
    {
        $submission = $this->service->store($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Created successfully',
            'data' => $submission
        ], 201);
    }

    public function spvAprovalJobSubmission(
        SpvApprovalJobSubmissionRequest $request,
        $id
    ) {
        $submission = JobSubmission::findOrFail($id);

        $updated = $this->service->approve(
            $submission,
            $request->status
        );

        return response()->json([
            'status' => 'success',
            'data' => $updated
        ]);
    }
    public function spvSelectJobSubmissions()
    {
        $result = $this->service->getTodaySubmissionsByDivision();

        return response()->json([
            'response_code' => 200,
            'status'        => 'success',
            'message'       => $result['message'],
            'data'          => $result['data'],
        ]);
    }
    public function siteManagerSelectJobSubmissions()
    {
        $result = $this->service->siteManagerSelectToday();

        return response()->json([
            'response_code' => 200,
            'status'        => 'success',
            'message'       => $result->isEmpty()
                ? 'Tidak ada submissions untuk hari ini'
                : 'Berhasil mengambil submissions untuk hari ini',
            'data'          => $result
        ], 200);
    }
    public function siteManagerApprovalJobSubmission(
        SpvApprovalJobSubmissionRequest $request,
        $id
    ) {

        $updated = $this->service->siteManagerApproveSupervisorAndStaffSubmission(
            $id,
            $request->status
        );

        return response()->json([
            'status' => 'success',
            'data' => $updated
        ]);
    }
    public function weeklySummary()
    {
        $data = $this->service->getWeeklySubmissionSummaryForCurrentUser();

        return response()->json([
            'data' => $data
        ]);
    }
    public function submissionSummary()
    {
        $data = $this->service->summary();

        return response()->json([
            'data' => $data
        ]);
    }
    public function destroy(JobSubmission $submission)
    {
        $this->service->destroy($submission);

        return response()->json([
            'status' => 'success',
            'message' => 'Deleted successfully'
        ]);
    }
}
