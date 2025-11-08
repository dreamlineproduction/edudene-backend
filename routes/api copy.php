<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\PageController as FrontPageController;
use App\Http\Controllers\Api\CourseController as FrontCourseController;

use App\Http\Controllers\Api\FileController;

use App\Http\Controllers\Api\User\UserAuthController;
use App\Http\Controllers\Api\User\UserProfileController;

use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\AdminProfileController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\CategoryLevelFourController;
use App\Http\Controllers\Api\Admin\CouponController;
use App\Http\Controllers\Api\Admin\FaqController;
use App\Http\Controllers\Api\Admin\PageController;
use App\Http\Controllers\Api\Admin\SubCategoryController;
use App\Http\Controllers\Api\Admin\SubSubCategoryController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\CourseReviewController;
use App\Http\Controllers\Api\Tutor\CourseChapterController;
use App\Http\Controllers\Api\Tutor\CourseController;

use App\Http\Controllers\Api\Tutor\CourseLessonController;
use App\Http\Controllers\Api\Tutor\TutorAuthController;

// Public API Routes



// Shared Routes  login not required
/*Route::prefix('v1')->group(function () {
        
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


    Route::post('tutor/login', [TutorAuthController::class, 'login']);


    Route::post('forgot-password', [UserAuthController::class, 'forgotPassword']);
    Route::get('check-token',[UserAuthController::class, 'checkIsValidToken']);
    Route::post('update-password', [UserAuthController::class, 'updatePassword']);
    //Route::post('upload', [FileController::class, 'upload']);
    Route::post('file/upload-image', [FileController::class, 'uploadImage']);
    Route::post('file/upload-video', [FileController::class, 'uploadVideo']);
    Route::post('file/upload-document', [FileController::class, 'uploadDocument']);
    Route::post('testing/{FILE_ID}', [FileController::class, 'imageTesting']);
});*/




// Shared Routes - login required only not role based
/*Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    Route::post('logout', [UserAuthController::class, 'logout']);

    // Course Routes
    Route::get('course', [CourseController::class, 'index']);
    Route::post('course/save-basic-information', [CourseController::class, 'saveBasicInformation']);
    Route::post('course/save-requirment', [CourseController::class, 'saveRequirment']);
    Route::post('course/save-outcome', [CourseController::class, 'saveOutcome']);
    Route::post('course/save-price', [CourseController::class, 'savePrice']);
    Route::post('course/save-media', [CourseController::class, 'saveMedia']);
    Route::post('course/save-seo', [CourseController::class, 'saveSeo']);
    
    // Course Chapter Routes
    Route::get('course/{COURSE_ID}/chapter', [CourseChapterController::class,'index']);
    Route::post('course/{COURSE_ID}/chapter', [CourseChapterController::class,'store']);
    Route::get('course/{COURSE_ID}/chapter/{CHAPTER_ID}', [CourseChapterController::class,'show']);
    Route::put('course/{COURSE_ID}/chapter/{CHAPTER_ID}', [CourseChapterController::class,'update']);
    Route::delete('course/{COURSE_ID}/chapter/{CHAPTER_ID}', [CourseChapterController::class,'destroy']);

    // Course Lesson Routes
    Route::get('course/{COURSE_ID}/chapter/{CHAPTER_ID}/lesson', [CourseLessonController::class,'index']);
    Route::post('course/{COURSE_ID}/chapter/{CHAPTER_ID}/lesson', [CourseLessonController::class,'store']);
    Route::put('course/{COURSE_ID}/chapter/{CHAPTER_ID}/lesson/{LESSON_ID}', [CourseLessonController::class,'update']);
    Route::get('course/{COURSE_ID}/chapter/{CHAPTER_ID}/lesson/{LESSON_ID}', [CourseLessonController::class,'show']);

    Route::delete('course/{COURSE_ID}/chapter/{CHAPTER_ID}/lesson/{LESSON_ID}', [CourseLessonController::class,'destroy']);    

});*/
require __DIR__ . '/api/front.php';
require __DIR__ . '/api/shared.php';
require __DIR__ . '/api/user.php';
require __DIR__.'/api/tutor.php';
require __DIR__.'/api/admin.php';

// User Routes - login required and role 1
/*Route::prefix('v1')->middleware(['auth:sanctum','role:1'])->group(function () {

    Route::post('user/save-basic-information', [UserProfileController::class, 'saveBasicInformation']);
    Route::post('user/save-education-qualification', [UserProfileController::class, 'saveEducationQualification']);
    Route::post('user/save-social-link', [UserProfileController::class, 'saveSocialLink']);
    Route::post('user/save-billing-information', [UserProfileController::class, 'bilingInformation']);
    Route::post('user/save-found-us', [UserProfileController::class, 'saveFoundUs']);
    Route::post('user/save-category', [UserProfileController::class, 'saveUserCategory']);

    // Change Password Route
    Route::post('user/change-password', [UserProfileController::class, 'changePassword']);

    // Course Review Routes
    Route::get('courses/review/{COURSE_ID}', [CourseReviewController::class, 'index']);
    Route::post('courses/review/{COURSE_ID}', [CourseReviewController::class, 'store']);
});*/


// Tutor Routes - login required and role 2
/*Route::prefix('v1')->middleware(['auth:sanctum','role:2'])->group(function () {

    // Tutor Profile Routes
    Route::post('tutor/course', [TutorAuthController::class, 'changePassword']);
});*/


// Admin Routes admin login required and admin role is 5
/*Route::prefix('v1')->middleware(['auth:sanctum','role:5'])->group(function () {   
    
    // Change Password Route
    Route::post('admin/change-password', [AdminProfileController::class, 'changePassword']);

    // Category Level 2 Routes
    Route::apiResource('admin/categories/level-two', SubCategoryController::class);  

    // Category Level 3 Routes
    Route::apiResource('admin/level-three', SubSubCategoryController::class);

    // Category Level 4 Routes
    Route::apiResource('admin/categories/level-four', CategoryLevelFourController::class);
        
    // Category Routes
    Route::apiResource('admin/categories', CategoryController::class);   
    
    // Faq Routes
    Route::apiResource('admin/faqs', FaqController::class);

    // Pages Routes
    Route::apiResource('admin/pages',PageController::class);

    // Coupon Routes
    Route::apiResource('admin/coupons', CouponController::class);
});*/



// Route::post('/user/change-password', [UserAuthController::class, 'changePassword'])->middleware('auth:sanctum');
// Route::post('/user/forgot-password', [UserAuthController::class, 'forgotPassword']);
// Route::post('/user/reset-password', [UserAuthController::class, 'resetPassword']);


?>