<?php

use App\Http\Controllers\Api\Admin\SettingController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\StateController;
use App\Http\Controllers\Api\TutorController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Tutor\CourseChapterController;
use App\Http\Controllers\Api\Tutor\CourseController;
use App\Http\Controllers\Api\Tutor\CourseLessonController;
use App\Http\Controllers\Api\User\UserAuthController;
use App\Http\Controllers\Api\School\ClassController;
use App\Http\Controllers\Api\School\ClassSessionController;


Route::prefix('v1')->group(function () {   
    Route::post('sent-otp-to-email', [UserAuthController::class, 'sendOtpToEmail']);
    Route::post('verify-otp', [UserAuthController::class, 'verifyOtp']);
    
    Route::post('file/upload-image', [FileController::class, 'uploadImage']);
    Route::post('file/upload-video', [FileController::class, 'uploadVideo']);
    Route::post('file/upload-document', [FileController::class, 'uploadDocument']);
    Route::post('testing/{FILE_ID}', [FileController::class, 'imageTesting']);


    Route::apiResource('countries', CountryController::class);
    Route::get('states/{COUNTRY_ID}', [StateController::class, 'index']);

    Route::get('settings', [SettingController::class, 'show']);
});

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    Route::post('logout', [UserAuthController::class, 'logout']);

    // Course Routes
    Route::get('course', [CourseController::class, 'index']);
	Route::get('course/{id}', [CourseController::class, 'show']);
	Route::post('course/save-by-title', [CourseController::class, 'createCourseByTitle']);
    Route::post('course/save-basic-information', [CourseController::class, 'saveBasicInformation']);
    Route::post('course/save-requirement', [CourseController::class, 'saveRequirement']);
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


    // Chat Contacts & Initialization
    Route::get('/chat/contacts', [ChatController::class, 'contacts']);
    
    // Starts a chat and returns chat_id
    Route::post('/chat/start/{otherUserId}', [ChatController::class, 'findOrCreateChat']); 

    // Message Management
    Route::get('/chat/{chat}', [ChatController::class, 'getMessages']); // Get history for a chat
    Route::post('/chat/{chat}/send', [ChatController::class, 'sendMessage']); // Send a new message    

	// Categories
	Route::get('categories', [CategoryController::class, 'index']); // Get main category
	Route::get('categories-level-two', [CategoryController::class, 'subCategory']); // Get sub category
	Route::get('categories-level-three', [CategoryController::class, 'subSubCategory']); // Get sub sub category
	Route::get('categories-level-four', [CategoryController::class, 'categoryLevelFour']); // Get category level four

    Route::get('tutors', [TutorController::class, 'index']); // Get all tutors

    Route::apiResource('classes', ClassController::class);
    Route::get('classes/{classId}/sessions', [ClassSessionController::class, 'index']);
    Route::put('classes/{classId}/sessions', [ClassSessionController::class, 'update']);

});