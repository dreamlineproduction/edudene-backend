<?php

use App\Http\Controllers\Api\Admin\AdminAuthController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Admin\AdminProfileController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\CategoryLevelFourController;
use App\Http\Controllers\Api\Admin\CouponController;
use App\Http\Controllers\Api\Admin\FaqSectionController;
use App\Http\Controllers\Api\Admin\FaqController;
use App\Http\Controllers\Api\Admin\PageController;
use App\Http\Controllers\Api\Admin\SettingController;
use App\Http\Controllers\Api\Admin\SubCategoryController;
use App\Http\Controllers\Api\Admin\SubSubCategoryController;


// Settings

Route::prefix('v1')->group(function () {   
    Route::post('admin/login', [AdminAuthController::class, 'login']);
        Route::apiResource('admin/coupons', CouponController::class);

});


Route::prefix('v1')->middleware(['auth:sanctum','role:5'])->group(function () {   
    
    // Change Password Route
    Route::post('admin/change-password', [AdminProfileController::class, 'changePassword']);

    // Category Level 2 Routes
    Route::apiResource('admin/categories/level-two', SubCategoryController::class);  

    // Category Level 3 Routes
    Route::apiResource('admin/categories/level-three', SubSubCategoryController::class);	

    // Category Level 4 Routes
    Route::apiResource('admin/categories/level-four', CategoryLevelFourController::class);
        
    // Category Routes
    Route::apiResource('admin/categories', CategoryController::class);   

	// Get Sub Categories of a specific category
	Route::get('/admin/get-sub-categories/{id}', [SubCategoryController::class, 'getSubCategories']);
	
	// Get Sub Sub Categories of a specific category
	Route::get('/admin/get-sub-sub-categories/{id}', [SubSubCategoryController::class, 'getSubSubCategories']);
    
    // Faq Sections Routes
    Route::apiResource('admin/sections/faqs', FaqSectionController::class);

    // Faq Routes
    Route::apiResource('admin/faqs', FaqController::class);

    
    // Pages Routes
    Route::apiResource('admin/pages',PageController::class);

    // Coupon Routes

    Route::post('admin/setting/website-setting', [SettingController::class, 'saveWebsite']);
    Route::post('admin/setting/stripe-setting', [SettingController::class, 'saveStripe']);
    Route::post('admin/setting/payment-setting', [SettingController::class, 'savePayment']);

});
