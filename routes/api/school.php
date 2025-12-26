<?php


use App\Http\Controllers\Api\School\SchoolAuthController;
use App\Http\Controllers\Api\School\SchoolTutorController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('school', [SchoolAuthController::class, 'store']);
    Route::post('school/login', [SchoolAuthController::class, 'login']);
    Route::post('school/register', [SchoolAuthController::class, 'register']);


});

//
Route::prefix('v1')->middleware(['auth:sanctum','role:3'])->group(function () {   
    
    
    Route::apiResource('school/teacher', SchoolTutorController::class);
});
?>