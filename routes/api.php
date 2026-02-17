<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AbsenceController;
use App\Http\Controllers\API\AuthenticationController;
use App\Http\Controllers\Api\OvertimeController;
use App\Http\Controllers\Api\JobCategoryController;
use App\Http\Controllers\Api\JobSubmissionController;


// --------------- Register and Login ----------------// 
// Route::post('register', 'AuthenticationController@register')->name('register');
// Route::post('login', 'AuthenticationController@login')->name('login');
Route::post('/login', [AuthenticationController::class, 'login'])->name('login');
// ------------------ Get Data ----------------------// 
Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/my-profile', [AuthenticationController::class, 'myProfile']);
    Route::post('/logout', [AuthenticationController::class, 'logout'])->name('logout');
    Route::get('/stat-overview', [AuthenticationController::class, 'statOverview']);
    // Hanya Gardener dan staff
    Route::middleware(['role:gardener|staff|supervisor'])->group(function () {
        Route::get('/job-categories', [JobCategoryController::class, 'index']);
        Route::get('/job-submission/my-submission-by-date', [JobSubmissionController::class, 'getMysubmissionBydate']);
        Route::get('/absence/my-absence', [AbsenceController::class, 'myabsence']);
        Route::get('/absence/my-absence-by-date', [AbsenceController::class, 'getMyAbsencesByDate']);
        Route::get('/overtime/my-overtime', [OvertimeController::class, 'myovertime']);
        Route::get('/job-submission/summary', [JobSubmissionController::class, 'submissionummary']);
        Route::post('/job-submission/store', [JobSubmissionController::class, 'store']);
        Route::post('/absence/store', [AbsenceController::class, 'store']);
        Route::post('/overtime/store', [OvertimeController::class, 'store']);
        Route::get('/overtime/my-overtime-by-date', [OvertimeController::class, 'getOvertimesByDate']);
        Route::delete('/job-submission/delete', [JobSubmissionController::class, 'destroy']);
        Route::delete('/overtime/delete', [OvertimeController::class, 'destroy']);
        Route::delete('/job-submission/delete', [JobSubmissionController::class, 'destroy']);
    });

    // Hanya Supervisor
    Route::middleware(['role:supervisor'])->group(function () {
        Route::get('/job-submission/spv-select-submissions', [JobSubmissionController::class, 'spvSelectJobSubmissions']);
        Route::put('/job-submission/approval-spv/{id}', [JobSubmissionController::class, 'spvAprovalJobSubmission']);
        Route::get('/overtime/spv-select-overtimes', [OvertimeController::class, 'spvSelectOvertime']);
        Route::put('/overtime/approval-spv/{id}', [OvertimeController::class, 'spvAprovalOvertime']);
        Route::get('/absence/spv-select-absences', [AbsenceController::class, 'spvSelectAbsences']);
        Route::put('/absence/approval-spv/{id}', [AbsenceController::class, 'spvApprovalAbsence']);
    });
    //hanya Site Manager
    Route::middleware(['role:site_manager'])->group(function () {
        Route::get('/job-submission', [JobSubmissionController::class, 'index']);
        Route::get('/absence', [AbsenceController::class, 'index']);
        Route::get('/overtime', [OvertimeController::class, 'index']);
    });
});
