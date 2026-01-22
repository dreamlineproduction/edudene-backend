<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Tutor\TutorAuthController;
use App\Http\Controllers\Api\Tutor\TutorAvailabilityController;
use App\Http\Controllers\Api\Tutor\TutorPricingController;
use App\Http\Controllers\Api\Tutor\TutorProfileController;

Route::prefix('v1')->group(function () {   
	Route::post('tutor/login', [TutorAuthController::class, 'login']);
    Route::post('tutor', [TutorAuthController::class, 'save']);
    Route::post('tutor/register', [TutorAuthController::class, 'register']);
});

Route::prefix('v1')->middleware(['auth:sanctum','role:2'])->group(function () {

    // Tutor Profile Routes
    Route::post('tutor/change-password', [TutorAuthController::class, 'changePassword']);
	Route::post('/tutor/enable-one-to-one', [TutorAvailabilityController::class, 'updateEnableOneToOne']);
	Route::post('/tutor/enable-trainer', [TutorAvailabilityController::class, 'updateEnableTrainer']);
	Route::post('/tutor/enable-courses', [TutorAvailabilityController::class, 'updateEnableCourses']);
	Route::post('/tutor/one-to-one-hourly-rate', [TutorPricingController::class, 'updateOneToOneHourlyRate']);
	Route::post('/tutor/trainer-hourly-rate', [TutorPricingController::class, 'updateTrainerHourlyRate']);
	Route::get('/tutor/profile', [TutorProfileController::class, 'show']);
});