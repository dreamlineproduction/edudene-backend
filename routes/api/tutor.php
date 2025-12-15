<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Tutor\TutorAuthController;


Route::prefix('v1')->group(function () {   
	Route::post('tutor/login', [TutorAuthController::class, 'login']);
    Route::post('tutor', [TutorAuthController::class, 'save']);
    Route::post('tutor/register', [TutorAuthController::class, 'register']);
});

Route::prefix('v1')->middleware(['auth:sanctum','role:2'])->group(function () {

    // Tutor Profile Routes
    Route::post('tutor/change-password', [TutorAuthController::class, 'changePassword']);
});