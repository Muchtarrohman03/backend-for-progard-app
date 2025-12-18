<?php

use PHPUnit\Util\PHP\Job;
use Illuminate\Http\Request;
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

    Route::get('/job-submissions', [JobSubmissionController::class, 'index']);
    Route::get('/absences', [AbsenceController::class, 'index']);
    Route::get('/absences/{id}', [AbsenceController::class, 'show']);
    Route::get('/overtimes', [OvertimeController::class, 'index']);
    Route::get('job-categories',  [JobCategoryController::class, 'index']);
    Route::get('job-categories/{id}',  [JobCategoryController::class, 'show']);

    // Hanya Gardener dan staff
    Route::middleware(['role:gardener|staff'])->group(function () {
        Route::get('/job-submissions/my-submissions', [JobSubmissionController::class, 'mysubmissions']);
        Route::get('/my-absences', [AbsenceController::class, 'myabsences']);
        Route::get('/overtimes/my-overtimes', [OvertimeController::class, 'myovertimes']);
        Route::get('/job-submissions/summary', [JobSubmissionController::class, 'submissionSummary']);
        Route::post('/job-submissions', [JobSubmissionController::class, 'store']);
        Route::post('/absences', [AbsenceController::class, 'store']);
        Route::post('/overtimes', [OvertimeController::class, 'store']);
    });

    // Hanya Supervisor
    Route::middleware(['role:supervisor'])->group(function () {
        Route::put('/job-submissions/{id}', [JobSubmissionController::class, 'update'])->whereNumber('id');
        Route::get('/division-submissions', [JobSubmissionController::class, 'divisionSubmissions']);
        Route::put('/absences/{id}', [AbsenceController::class, 'update']);
        Route::put('/overtimes/{id}', [OvertimeController::class, 'update']);
    });
});
