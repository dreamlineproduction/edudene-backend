<?php

use App\Http\Controllers\Api\User\UserAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\FaqController;
use App\Http\Controllers\Api\Admin\SubCategoryController;
use App\Http\Controllers\Api\Admin\SubSubCategoryController;

Route::get('/testing', function () {
    return response()->json([
        'status' => 200,
        'message' => 'API is fetched successfully',
    ]);
});

// User Auth Routes
Route::post('/v1/user/register', [UserAuthController::class, 'register']);
Route::post('/v1/user/save-basic-information', [UserAuthController::class, 'saveBasicInformation']);
Route::post('/v1/user/save-education-qualification', [UserAuthController::class, 'saveEducationQualification']);
Route::post('/v1/user/save-social-link', [UserAuthController::class, 'saveSocialLink']);
Route::post('/v1/user/save-billing-information', [UserAuthController::class, 'bilingInformation']);
Route::post('/v1/user/save-found-us', [UserAuthController::class, 'saveFoundUs']);
Route::post('/v1/user/save-category', [UserAuthController::class, 'saveUserCategory']);



Route::post('/v1/user/login', [UserAuthController::class, 'login']);





// Admin Auth Routes
Route::post('/v1/admin/login', [AdminAuthController::class, 'login']);

// Admin Routes Start Here
Route::middleware('auth:sanctum')->group(function () {   
    // Category Routes
    Route::apiResource('/v1/admin/categories', CategoryController::class);   

    // Sub Category Routes
    Route::apiResource('/v1/admin/subcategories', SubCategoryController::class);   
    

    // Sub Sub Category Routes
    Route::apiResource('/v1/admin/subsubcategories', SubSubCategoryController::class);

    // Faq Routes
    Route::apiResource('/v1/admin/faqs', FaqController::class);
});

// Route::post('/user/change-password', [UserAuthController::class, 'changePassword'])->middleware('auth:sanctum');
// Route::post('/user/forgot-password', [UserAuthController::class, 'forgotPassword']);
// Route::post('/user/reset-password', [UserAuthController::class, 'resetPassword']);



Route::post('/v1/logout', [UserAuthController::class, 'logout'])->middleware('auth:sanctum');
?>