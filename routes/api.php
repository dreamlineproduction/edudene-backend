<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\PageController as FrontPageController;

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

// Public API Routes
Route::post('/v1/user/register', [UserAuthController::class, 'register']);
Route::post('/v1/user/login', [UserAuthController::class, 'login']);
Route::post('/v1/admin/login', [AdminAuthController::class, 'login']);
Route::get('/v1/pages/', [FrontPageController::class, 'index']);
Route::get('/v1/pages/{slug}', [FrontPageController::class, 'show']);



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



Route::post('/v1/logout', [UserAuthController::class, 'logout'])->middleware('auth:sanctum');
?>