<?php

use App\Http\Controllers\Api\School\SchoolAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('school', [SchoolAuthController::class, 'store']);
    Route::post('school/register', [SchoolAuthController::class, 'register']);
});
?>