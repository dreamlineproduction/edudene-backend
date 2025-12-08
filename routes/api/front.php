<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\SettingController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\User\UserAuthController;
use App\Http\Controllers\Api\PageController as FrontPageController;
use App\Http\Controllers\Api\CourseController as FrontCourseController;

use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\StateController;
use App\Http\Controllers\Api\Tutor\TutorAuthController;

Route::prefix('v1')->group(function () {
        
    Route::get('pages/', [FrontPageController::class, 'index']);
    Route::get('pages/{slug}', [FrontPageController::class, 'show']);
    Route::get('contact/topics', [ContactController::class, 'topics']);
    Route::post('contact', [ContactController::class, 'store']);
    Route::post('sent-otp-to-email', [UserAuthController::class, 'sendOtpToEmail']);
    Route::post('verify-otp', [UserAuthController::class, 'verifyOtp']);

    Route::get('courses', [FrontCourseController::class, 'index']);
    Route::get('courses/{SLUG}', [FrontCourseController::class, 'show']);


    Route::post('user/register', [UserAuthController::class, 'register']);
    Route::post('user/login', [UserAuthController::class, 'login']);

    Route::post('admin/login', [AdminAuthController::class, 'login']);


    Route::post('tutor', [TutorAuthController::class, 'save']);
    Route::post('tutor/login', [TutorAuthController::class, 'login']);
    Route::post('tutor/register', [TutorAuthController::class, 'register']);


    Route::post('forgot-password', [UserAuthController::class, 'forgotPassword']);
    Route::get('check-token',[UserAuthController::class, 'checkIsValidToken']);
    Route::post('update-password', [UserAuthController::class, 'updatePassword']);
    //Route::post('upload', [FileController::class, 'upload']);
    Route::post('file/upload-image', [FileController::class, 'uploadImage']);
    Route::post('file/upload-video', [FileController::class, 'uploadVideo']);
    Route::post('file/upload-document', [FileController::class, 'uploadDocument']);
    Route::post('testing/{FILE_ID}', [FileController::class, 'imageTesting']);


    Route::apiResource('countries', CountryController::class);
    //Route::apiResource('states', StateController::class);
    Route::get('states/{COUNTRY_ID}', [StateController::class, 'index']);

    Route::get('settings', [SettingController::class, 'show']);
});
?>