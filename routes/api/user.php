<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\UserProfileController;
use App\Http\Controllers\Api\CourseReviewController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\User\UserVerificationController as FrontUserVerificationController;

Route::prefix('v1')->middleware(['auth:sanctum','role:1'])->group(function () {
    Route::get('user/profile', [UserProfileController::class, 'show']);

    Route::post('user/save-password', [UserProfileController::class, 'savePassword']);
    Route::post('user/save-basic-information', [UserProfileController::class, 'saveBasicInformation']);
    Route::post('user/save-education-qualification', [UserProfileController::class, 'saveEducationQualification']);
    Route::post('user/save-social-link', [UserProfileController::class, 'saveSocialLink']);
    Route::post('user/save-billing-information', [UserProfileController::class, 'bilingInformation']);
    Route::post('user/save-found-us', [UserProfileController::class, 'saveFoundUs']);
    Route::post('user/save-category', [UserProfileController::class, 'saveUserCategory']);


    Route::post('user/kyc', [FrontUserVerificationController::class, 'kyc']);
	Route::post('user/face-verification', [FrontUserVerificationController::class, 'faceVerification']);

    // Change Password Route
    Route::post('user/change-password', [UserProfileController::class, 'changePassword']);

    // Course Review Routes
    Route::get('courses/review/{COURSE_ID}', [CourseReviewController::class, 'index']);
    Route::post('courses/review/{COURSE_ID}', [CourseReviewController::class, 'store']);


    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{id}', [OrderController::class, 'show']);
});
