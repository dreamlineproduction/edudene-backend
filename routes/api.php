<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

use App\Http\Controllers\Api\PageController as FrontPageController;
use App\Http\Controllers\Api\FileController;

use App\Http\Controllers\Api\User\UserAuthController;
use App\Http\Controllers\Api\User\UserProfileController;

use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\AdminProfileController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\CouponController;
use App\Http\Controllers\Api\Admin\FaqController;
use App\Http\Controllers\Api\Admin\PageController;
use App\Http\Controllers\Api\Admin\SubCategoryController;
use App\Http\Controllers\Api\Admin\SubSubCategoryController;
use App\Http\Controllers\Api\Tutor\CourseChapterController;
use App\Http\Controllers\Api\Tutor\CourseController;
use App\Http\Controllers\Api\Tutor\CourseLessonController;
use App\Http\Controllers\Api\Tutor\TutorAuthController;

// Public API Routes

//Route::get('/testing',[CourseController::class, 'saveMedia']);

Route::get('/v1/pages/', [FrontPageController::class, 'index']);
Route::get('/v1/pages/{slug}', [FrontPageController::class, 'show']);

//Public User Routes
Route::post('/v1/user/register', [UserAuthController::class, 'register']);
Route::post('/v1/user/login', [UserAuthController::class, 'login']);

// Public Admin Routes
Route::post('/v1/admin/login', [AdminAuthController::class, 'login']);


// Public Tutor Routes
Route::post('/v1/tutor/login', [TutorAuthController::class, 'login']);

// Common Routes Admin,User Tutor, and School
Route::post('/v1/forgot-password', [UserAuthController::class, 'forgotPassword']);
Route::get('v1/check-token',[UserAuthController::class, 'checkIsValidToken']);
Route::post('/v1/update-password', [UserAuthController::class, 'updatePassword']);
//Route::post('/v1/upload', [FileController::class, 'upload']);
Route::post('/v1/file/upload-image', [FileController::class, 'uploadImage']);
Route::post('/v1/file/upload-video', [FileController::class, 'uploadVideo']);
Route::post('/v1/testing/{FILE_ID}', [FileController::class, 'imageTesting']);


// Common Routes - login required only not role based
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/v1/logout', [UserAuthController::class, 'logout']);

    // Course Routes
    Route::get('/v1/course', [CourseController::class, 'index']);
    Route::post('/v1/course/save-basic-information', [CourseController::class, 'saveBasicInformation']);
    Route::post('/v1/course/save-requirment', [CourseController::class, 'saveRequirment']);
    Route::post('/v1/course/save-outcome', [CourseController::class, 'saveOutcome']);
    Route::post('/v1/course/save-price', [CourseController::class, 'savePrice']);
    Route::post('/v1/course/save-media', [CourseController::class, 'saveMedia']);
    Route::post('/v1/course/save-seo', [CourseController::class, 'saveSeo']);
    
    // Course Chapter Routes
    Route::get('/v1/course/{COURSE_ID}/chapter', [CourseChapterController::class,'index']);
    Route::post('/v1/course/{COURSE_ID}/chapter', [CourseChapterController::class,'store']);
    Route::get('/v1/course/{COURSE_ID}/chapter/{CHAPTER_ID}', [CourseChapterController::class,'show']);
    Route::put('/v1/course/{COURSE_ID}/chapter/{CHAPTER_ID}', [CourseChapterController::class,'update']);
    Route::delete('/v1/course/{COURSE_ID}/chapter/{CHAPTER_ID}', [CourseChapterController::class,'destroy']);

    // Course Lesson Routes
    Route::get('/v1/course/{COURSE_ID}/chapter/{CHAPTER_ID}/lesson', [CourseLessonController::class,'index']);
    Route::post('/v1/course/{COURSE_ID}/chapter/{CHAPTER_ID}/lesson', [CourseLessonController::class,'store']);
    Route::get('/v1/course/{COURSE_ID}/chapter/{CHAPTER_ID}/lesson/{LESSON_ID}', [CourseLessonController::class,'show']);

    Route::delete('/v1/course/{COURSE_ID}/chapter/{CHAPTER_ID}/lesson/{LESSON_ID}', [CourseLessonController::class,'destroy']);

});


// User Routes - login required and role 1
Route::middleware(['auth:sanctum','role:1'])->group(function () {

    Route::post('/v1/user/save-basic-information', [UserProfileController::class, 'saveBasicInformation']);
    Route::post('/v1/user/save-education-qualification', [UserProfileController::class, 'saveEducationQualification']);
    Route::post('/v1/user/save-social-link', [UserProfileController::class, 'saveSocialLink']);
    Route::post('/v1/user/save-billing-information', [UserProfileController::class, 'bilingInformation']);
    Route::post('/v1/user/save-found-us', [UserProfileController::class, 'saveFoundUs']);
    Route::post('/v1/user/save-category', [UserProfileController::class, 'saveUserCategory']);

    // Change Password Route
    Route::post('/v1/user/change-password', [UserProfileController::class, 'changePassword']);

    // Forgot Password Route
});


// Tutor Routes - login required and role 2
Route::middleware(['auth:sanctum','role:2'])->group(function () {

    // Tutor Profile Routes
    Route::post('/v1/tutor/course', [TutorAuthController::class, 'changePassword']);
});


// Admin Routes admin login required and admin role is 5
Route::middleware(['auth:sanctum','role:5'])->group(function () {   
    
    // Change Password Route
    Route::post('/v1/admin/change-password', [AdminProfileController::class, 'changePassword']);

    // Category Routes
    Route::apiResource('/v1/admin/categories', CategoryController::class);   

    // Sub Category Routes
    Route::apiResource('/v1/admin/subcategories', SubCategoryController::class);   
    

    // Sub Sub Category Routes
    Route::apiResource('/v1/admin/subsubcategories', SubSubCategoryController::class);

    // Faq Routes
    Route::apiResource('/v1/admin/faqs', FaqController::class);

    // Pages Routes
    Route::apiResource('/v1/admin/pages',PageController::class);

    // Coupon Routes
    Route::apiResource('/v1/admin/coupons', CouponController::class);

});

// Route::post('/user/change-password', [UserAuthController::class, 'changePassword'])->middleware('auth:sanctum');
// Route::post('/user/forgot-password', [UserAuthController::class, 'forgotPassword']);
// Route::post('/user/reset-password', [UserAuthController::class, 'resetPassword']);


?>