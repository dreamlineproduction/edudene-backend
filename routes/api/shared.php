<?php

use App\Http\Controllers\Api\ChatController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Tutor\CourseChapterController;
use App\Http\Controllers\Api\Tutor\CourseController;
use App\Http\Controllers\Api\Tutor\CourseLessonController;
use App\Http\Controllers\Api\User\UserAuthController;


Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
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


    Route::get('chats', [ChatController::class, 'index']);
    Route::post('chats/start', [ChatController::class, 'startChat']);
    Route::get('chats/{chatId}/messages', [ChatController::class, 'getMessages']);
    Route::post('chats/{chatId}/message', [ChatController::class, 'sendMessage']);

});