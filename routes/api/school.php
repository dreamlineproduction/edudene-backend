<?php


use App\Http\Controllers\Api\School\SchoolAuthController;
use App\Http\Controllers\Api\School\SchoolTutorController;
use App\Http\Controllers\Api\School\ClassController;
use App\Http\Controllers\Api\School\ClassSessionController;
use App\Http\Controllers\Api\School\SchoolController;
use App\Http\Controllers\Api\School\SchoolThemeController;
use App\Http\Controllers\Api\School\SchoolInvitationController;

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('school', [SchoolAuthController::class, 'store']);
    Route::post('school/login', [SchoolAuthController::class, 'login']);
    Route::post('school/register', [SchoolAuthController::class, 'register']);
});

Route::prefix('v1')->middleware(['auth:sanctum','role:4'])->group(function () {   
   Route::get('school/tutor-profile', [SchoolTutorController::class, 'show']);
   Route::put('school/tutor-profile', [SchoolTutorController::class, 'updateV2']);   
});

//
Route::prefix('v1')->middleware(['auth:sanctum','role:3'])->group(function () {   

    Route::post('school/send-invitation',[SchoolInvitationController::class, 'sendInvitation']);

    Route::get('school/classes/teachers',[ClassController::class, 'teacher']);
    Route::get('school/freelancer',[ClassController::class, 'freelancerTeacher']);

    Route::apiResource('school/classes', ClassController::class);
    Route::apiResource('school/teachers', SchoolTutorController::class);



    // Change Password Route
    Route::post('school/change-password', [SchoolController::class, 'changePassword']);
    Route::get('school/{SCHOOL_ID}', [SchoolController::class, 'show']);
    Route::put('school/{SCHOOL_ID}', [SchoolController::class, 'update']);  
    
    // School Theme
    Route::put('school/{SCHOOL_ID}/theme', [SchoolThemeController::class, 'update']);
    Route::get('school/{SCHOOL_ID}/theme', [SchoolThemeController::class, 'show']);



});


?>