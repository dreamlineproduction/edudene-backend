<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserAuthController;

Route::get('/testing', function () {
    return response()->json([
        'status' => 200,
        'message' => 'API is fetched successfully',
    ]);
});

Route::post('/user/register', [UserAuthController::class, 'register']);
Route::post('/user/login', [UserAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/user/logout', [UserAuthController::class, 'logout']);
});

// Route::post('/user/change-password', [UserAuthController::class, 'changePassword'])->middleware('auth:sanctum');
// Route::post('/user/forgot-password', [UserAuthController::class, 'forgotPassword']);
// Route::post('/user/reset-password', [UserAuthController::class, 'resetPassword']);

?>