<?php


use App\Http\Controllers\Api\School\SchoolAuthController;
use App\Http\Controllers\Api\School\SchoolTutorController;
use App\Http\Controllers\Api\School\ClassController;
use App\Http\Controllers\Api\School\ClassSessionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('school', [SchoolAuthController::class, 'store']);
    Route::post('school/login', [SchoolAuthController::class, 'login']);
    Route::post('school/register', [SchoolAuthController::class, 'register']);


});

//
Route::prefix('v1')->middleware(['auth:sanctum','role:3'])->group(function () {   
    
    Route::apiResource('school/classes', ClassController::class);
    //Route::get('school/classes/{classId}/sessions', [ClassSessionController::class, 'index']);
    //Route::put('school/classes/{classId}/sessions', [ClassSessionController::class, 'update']);    
    Route::apiResource('school/teachers', SchoolTutorController::class);
});
?>