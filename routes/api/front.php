<?php

use App\Http\Controllers\Api\CartController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\User\UserAuthController;
use App\Http\Controllers\Api\PageController as FrontPageController;
use App\Http\Controllers\Api\CourseController as FrontCourseController;
use App\Http\Controllers\Api\FaqController as FrontFaqController;
use App\Http\Controllers\Api\School\SchoolController;
use App\Http\Controllers\Api\User\ChangeEmailRequestController as FrontChangeEmailRequestController;


Route::prefix('v1')->group(function () {
        
    Route::get('pages/', [FrontPageController::class, 'index']);
    Route::get('pages/{slug}', [FrontPageController::class, 'show']);
    Route::get('contact/topics', [ContactController::class, 'topics']);
    Route::post('contact', [ContactController::class, 'store']);
    

    Route::get('courses', [FrontCourseController::class, 'index']);
    Route::get('courses/{SLUG}', [FrontCourseController::class, 'show']);


    Route::post('user/register', [UserAuthController::class, 'register']);
    Route::post('user/login', [UserAuthController::class, 'login']);

    Route::post('google-login', [UserAuthController::class, 'googleLogin']);
    Route::post('linkedin-login', [UserAuthController::class, 'linkedinLogin']);
    Route::post('facebook-login', [UserAuthController::class, 'faceookLogin']);


    Route::post('forgot-password', [UserAuthController::class, 'forgotPassword']);
    Route::post('check-token',[UserAuthController::class, 'checkIsValidToken']);
    Route::post('update-password', [UserAuthController::class, 'updatePassword']);
    //Route::post('upload', [FileController::class, 'upload']);
    

    // 
    Route::get('faqs', [FrontFaqController::class, 'index']);


    // 
    Route::get('user/email-change', [FrontChangeEmailRequestController::class, 'index']);
    Route::post('user/email-change', [FrontChangeEmailRequestController::class, 'store']);

    Route::get('school/front', [SchoolController::class, 'index']);
    Route::get('school/front/{SCHOOL_SLUG}', [SchoolController::class, 'showFront']);
    Route::get('school/front/classes/{CLASS_ID}/timeline', [SchoolController::class, 'viewTimelineModal']);
    
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'add']);
        Route::post('/update-qty', [CartController::class, 'updateQty']);
        Route::delete('/remove/{id}', [CartController::class, 'remove']);
        Route::delete('/clear', [CartController::class, 'clear']);
    });
});


?>