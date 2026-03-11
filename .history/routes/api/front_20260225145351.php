<?php

use App\Http\Controllers\Api\CartController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\User\UserAuthController;
use App\Http\Controllers\Api\TutorController;
use App\Http\Controllers\Api\PageController as FrontPageController;
use App\Http\Controllers\Api\CourseController as FrontCourseController;
use App\Http\Controllers\Api\FaqController as FrontFaqController;
use App\Http\Controllers\Api\SchoolController as FrontSchoolController;
use App\Http\Controllers\Api\User\ChangeEmailRequestController as FrontChangeEmailRequestController;


Route::prefix('v1')->group(function () {
        
    Route::get('pages/', [FrontPageController::class, 'index']);
    Route::get('pages/{slug}', [FrontPageController::class, 'show']);
    Route::get('contact/topics', [ContactController::class, 'topics']);
    Route::post('contact', [ContactController::class, 'store']);
    

    Route::get('front/courses', [FrontCourseController::class, 'index']);
    Route::get('front/popular/courses', [FrontCourseController::class, 'popularCourse']);
    Route::get('front/courses/{SLUG}', [FrontCourseController::class, 'show']);

    // Front Tutor Routes Define
    Route::get('front/tutors', [TutorController::class, 'index']);
    Route::get('front/popular/tutors', [TutorController::class, 'popularTeacher']);
    Route::get('front/tutors/{TEACHER_SLUG}', [TutorController::class, 'show']);
    Route::get('front/tutors/{TEACHER_SLUG}/classes', [TutorController::class, 'classes']);
    Route::get('front/tutors/{TEACHER_SLUG}/courses', [TutorController::class, 'course']);
    Route::get('front/tutors/{TEACHER_SLUG}/month-wise-slots', [TutorController::class, 'getMonthWiseSlots']);
    Route::get('front/tutors/{TEACHER_SLUG}/one-on-one', [TutorController::class, 'getOneOnOneCalendar']);
    Route::get('front/tutors/{TEACHER_SLUG}/one-on-one-slots/{SLOT_DATE}', [TutorController::class, 'oneOnOneSlot']);

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

    // Front School Routes Define
    Route::get('front/school', [FrontSchoolController::class, 'index']);
    Route::get('front/popular/school', [FrontSchoolController::class, 'popularSchool']);
    Route::get('front/school/{SCHOOL_SLUG}', [FrontSchoolController::class, 'show']);
    Route::get('front/school/classes/{CLASS_ID}/timeline', [FrontSchoolController::class, 'viewTimelineModal']);
    Route::get('front/school/{SCHOOL_SLUG}/classes', [FrontSchoolController::class, 'classes']);
    Route::get('front/school/{SCHOOL_SLUG}/courses', [FrontSchoolController::class, 'course']);
    Route::get('front/school/{SCHOOL_SLUG}/teachers', [FrontSchoolController::class, 'teachers']);
    


    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'add']);
        Route::post('/update-qty', [CartController::class, 'updateQty']);
        Route::delete('/remove/{id}', [CartController::class, 'remove']);
        Route::delete('/remove-via-item-id/{ITEM_ID}/{TYPE}', [CartController::class, 'removeViaItemId']);
        Route::delete('/clear', [CartController::class, 'clear']);
    });
    
});


?>