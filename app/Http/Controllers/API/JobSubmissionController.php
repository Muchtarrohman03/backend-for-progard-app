<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobSubmission\GetJobSubmissionByDateRequest;
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
}
