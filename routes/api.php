<?php

use App\Http\Controllers\MainControllers\OtpController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainControllers\ServiceCategoryController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');


use App\Http\Controllers\MainControllers\UserController;

############################## Users #######################################################
Route::post('/auth/register', [UserController::class, 'registerUser']);
Route::post('/auth/login', [UserController::class, 'login']);
Route::middleware('auth:api')->post('/auth/logout', [UserController::class, 'logout']);
Route::middleware('auth:api')->get('/users', [UserController::class, 'index']);
########################end Users###########################################################


/* ------------------------------------------------------------------------------------------------
 * -------------------------------------Otp Routes table name is "Otps"----------------------------
 * --------------------------------------------------------------------------------------------*/

Route::post("otp_store", [OtpController::class, 'store']);
Route::post("otp_show", [OtpController::class, 'show']);
Route::put("otp_update", [OtpController::class, 'update']);
Route::post("otp_delete", [OtpController::class, 'destroy']);
/*---------------------------------------------------------------------------------------------
   * ------------------------------------End OTP-----------------------------------------------
   * ------------------------------------------------------------------------------------------*/



Route::middleware('auth:api')->group(function () {

    // عرض (للكل)
    Route::get('/service-categories', [ServiceCategoryController::class, 'index']);
    Route::get('/service-categories/{id}', [ServiceCategoryController::class, 'show']);



    // إدارة (أدمن فقط)
    Route::middleware('role:admin')->group(function () {
        Route::post('/service-categories', [ServiceCategoryController::class, 'store']);
        Route::delete('/service-categories/{id}', [ServiceCategoryController::class, 'destroy']);
        Route::get('/service-categories/deleted', [ServiceCategoryController::class, 'indexDeleted']);
        Route::get('/service-categories/all',    [ServiceCategoryController::class, 'indexWithTrashed']);
        Route::put('/service-categories/{id}',   [ServiceCategoryController::class, 'update']);
        Route::delete('/service-categories/{id}', [ServiceCategoryController::class, 'destroy']);
        Route::post('/service-categories/{id}/restore',     [ServiceCategoryController::class, 'restore']);
        Route::delete('/service-categories/{id}/force',     [ServiceCategoryController::class, 'forceDelete']);
    });
});
