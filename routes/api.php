<?php

use App\Http\Controllers\Api\AbsenceController;
use App\Http\Controllers\API\AuthenticationController;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\JobCategoryController;
use App\Http\Controllers\Api\JobSubmissionController;
use App\Http\Controllers\Api\OvertimeController;
use App\Http\Controllers\Api\PositionController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;


// --------------- Login ----------------// 
// Route::post('login', 'AuthenticationController@login')->name('login');
Route::post('/login', [AuthenticationController::class, 'login'])->name('login');
// ------------------ Get Data ----------------------// 
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/fcm-token', [FcmTokenController::class, 'update']);
    Route::delete('/fcm-token', [FcmTokenController::class, 'destroy']);
    Route::get('/my-profile', [AuthenticationController::class, 'myProfile']);
    Route::post('/logout', [AuthenticationController::class, 'logout'])->name('logout');
    Route::get('/stat-overview', [AuthenticationController::class, 'statOverview']);
    Route::get('/position/self', [PositionController::class, 'selfPosition']);
    // Hanya Gardener, staff dan Supervisor
    Route::middleware(['role:gardener|staff|supervisor'])->group(function () {
        Route::get('position/force-flag', [PositionController::class, 'checkForceFlag']);
        Route::post('/position/store', [PositionController::class, 'store']);
        Route::get('/job-categories', [JobCategoryController::class, 'index']);
        Route::get('/job-submission/my-submission-by-date', [JobSubmissionController::class, 'getMysubmissionBydate']);
        Route::get('/job-submission/summary', [JobSubmissionController::class, 'submissionSummary']);
        Route::post('/job-submission/store', [JobSubmissionController::class, 'store']);
        Route::get('/job-submission/weekly-summary', [JobSubmissionController::class, 'weeklySummary']);
        Route::delete('/job-submission/delete', [JobSubmissionController::class, 'destroy']);
        Route::get('/absence/my-absence', [AbsenceController::class, 'myabsence']);
        Route::get('/absence/my-absence-by-date', [AbsenceController::class, 'getMyAbsencesByDate']);
        Route::post('/absence/store', [AbsenceController::class, 'store']);
        Route::get('/overtime/my-overtime', [OvertimeController::class, 'myovertime']);
        Route::post('/overtime/store', [OvertimeController::class, 'store']);
        Route::get('/overtime/my-overtime-by-date', [OvertimeController::class, 'getMyOvertimesByDate']);
        Route::delete('/overtime/delete', [OvertimeController::class, 'destroy']);
    });

    // Hanya Supervisor
    Route::middleware(['role:supervisor'])->group(function () {
        Route::get('/position/division', [PositionController::class, 'divisionGardeners']);
        Route::get('/job-submission/spv-select-submissions', [JobSubmissionController::class, 'spvSelectJobSubmissions']);
        Route::put('/job-submission/approval-spv/{id}', [JobSubmissionController::class, 'spvAprovalJobSubmission']);
        Route::get('/overtime/spv-select-overtimes', [OvertimeController::class, 'spvSelectOvertime']);
        Route::put('/overtime/approval-spv/{id}', [OvertimeController::class, 'spvAprovalOvertime']);
        Route::get('/absence/spv-select-absences', [AbsenceController::class, 'spvSelectAbsences']);
        Route::put('/absence/approval-spv/{id}', [AbsenceController::class, 'spvApprovalAbsence']);
    });
    //hanya Site Manager
    Route::middleware(['role:site_manager'])->group(function () {
        Route::get('/position/all', [PositionController::class, 'allPositions']);
        Route::get('/job-submission', [JobSubmissionController::class, 'index']);
        Route::get('/absence', [AbsenceController::class, 'index']);
        Route::get('/overtime', [OvertimeController::class, 'index']);
        Route::get('/job-submission/get-job-submissions-by-division-and-date', [JobSubmissionController::class, 'getByDivisionAndDate']);
        Route::get('/job-submission/sm-select-submissions', [JobSubmissionController::class, 'siteManagerSelectJobSubmissions']);
        Route::put('/job-submission/approval-sm/{id}', [JobSubmissionController::class, 'siteManagerApprovalJobSubmission']);
        Route::get('/overtime/get-overtimes-by-division-and-date', [OvertimeController::class, 'getOvertimesByDivisionAndDate']);
        Route::get('/overtime/sm-select-overtimes', [OvertimeController::class, 'siteManagerSelectOvertime']);
        Route::put('/overtime/approval-sm/{id}', [OvertimeController::class, 'siteManagerApprovalOvertime']);
        Route::get('/absence/get-absences-by-division-and-date', [AbsenceController::class, 'getAbsencesByDivisionAndDate']);
        Route::get('/absence/sm-select-absences', [AbsenceController::class, 'siteManagerSelectAbsences']);
        Route::put('/absence/approval-sm/{id}', [AbsenceController::class, 'siteManagerApprovalAbsence']);
    });
    Route::middleware(['role:site_manager|supervisor'])->group(function () {
        Route::post('/position/force-update', [PositionController::class, 'forceUpdate']);
    });
});
