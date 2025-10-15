<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserAuthController;

Route::get('/user', function () {
    return response()->json([
        'status' => 200,
        'message' => 'User fetched successfully',
    ]);
});

Route::post('/user/register', [UserAuthController::class, 'register']);
Route::post('/user/login', [UserAuthController::class, 'login']);

?>