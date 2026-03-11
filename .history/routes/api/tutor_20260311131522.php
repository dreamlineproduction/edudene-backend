<?php

use App\Http\Controllers\Api\Tutor\OneOnOneClassSlotController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Tutor\TutorAuthController;
use App\Http\Controllers\Api\Tutor\TutorAvailabilityController;
use App\Http\Controllers\Api\Tutor\TutorPricingController;
use App\Http\Controllers\Api\Tutor\TutorProfileController;
use App\Http\Controllers\Api\TutorController;

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

	// Tutor Settings
	Route::post('tutor/expertise-subjects', [TutorController::class, 'saveTutorSubjects']); // Save tutor's selected subjects/categories
    Route::get('tutor/subjects', [TutorController::class, 'getTutorSubjects']); // Fetch tutor's expertise subjects

	// Tutor One on One Class Slots
	Route::get('/tutor/slots', [OneOnOneClassSlotController::class, 'index']);
	Route::post('/tutor/slots', [OneOnOneClassSlotController::class, 'store']);
    Route::put('/tutor/slots/{slot}', [OneOnOneClassSlotController::class, 'update']);
    Route::delete('/tutor/slots/{slot}', [OneOnOneClassSlotController::class, 'destroy']);
});
