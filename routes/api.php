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
Route::post('/v1/user/login', [UserAuthController::class, 'login']);

// Admin Auth Routes
Route::post('/v1/admin/login', [AdminAuthController::class, 'login']);

 


Route::middleware('auth:sanctum')->group(function () {
   
    Route::post('/v1/logout', [UserAuthController::class, 'logout']);
    

    // Admin Routes
    Route::get('/v1/admin/categories', [CategoryController::class, 'index']);
    Route::post('/v1/admin/categories', [CategoryController::class, 'store']);
    Route::put('/v1/admin/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/v1/admin/categories/{id}', [CategoryController::class, 'destroy']);


    Route::get('/v1/admin/subcategories', [SubCategoryController::class, 'index']);
    Route::post('/v1/admin/subcategories', [SubCategoryController::class, 'store']);
    Route::put('/v1/admin/subcategories/{id}', [SubCategoryController::class, 'update']);
    Route::delete('/v1/admin/subcategories/{id}', [SubCategoryController::class, 'destroy']);

    // Sub Sub Category Routes
    Route::get('/v1/admin/subsubcategories', [SubSubCategoryController::class, 'index']);
    Route::post('/v1/admin/subsubcategories', [SubSubCategoryController::class, 'store']);
    Route::put('/v1/admin/subsubcategories/{id}', [SubSubCategoryController::class, 'update']);
    Route::delete('/v1/admin/subsubcategories/{id}', [SubSubCategoryController::class, 'destroy']);

    // Faq Routes
    Route::apiResource('/v1/admin/faqs', FaqController::class);


    
//     Route::get('/v1/user/data', function () {
//      $user = auth('sanctum')->user();

//        // Change password logic here
//        return response()->json([
//            'status' => 200,
//            'user' => $user,
//            'message' => 'Password changed successfully',
//        ]);
//    });
});

// Route::post('/user/change-password', [UserAuthController::class, 'changePassword'])->middleware('auth:sanctum');
// Route::post('/user/forgot-password', [UserAuthController::class, 'forgotPassword']);
// Route::post('/user/reset-password', [UserAuthController::class, 'resetPassword']);

?>