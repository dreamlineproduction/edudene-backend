<?php

use App\Http\Controllers\Api\Tutor\TutorAuthController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->middleware(['auth:sanctum','role:2'])->group(function () {

    // Tutor Profile Routes
    Route::post('tutor/change-password', [TutorAuthController::class, 'changePassword']);

    
});