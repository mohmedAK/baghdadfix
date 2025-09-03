<?php

use App\Http\Controllers\MainControllers\AreaController;
use App\Http\Controllers\MainControllers\OtpController;
use App\Http\Controllers\MainControllers\ServiceController;
use App\Http\Controllers\MainControllers\StateController;
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

    Route::get('/services',               [ServiceController::class, 'index']);
    Route::get('/services/{id}',          [ServiceController::class, 'show']);

    Route::get('/get_states',              [StateController::class, 'index']);

    Route::get('/get_areas',               [AreaController::class, 'index']);
    Route::get('/states/{stateId}/areas', [AreaController::class, 'byState']); // مناطق محافظة


    // إدارة (أدمن فقط)
    Route::middleware('role:admin')->group(function () {
        ######################### Service Categories #########################
        Route::post('/service-categories', [ServiceCategoryController::class, 'store']);
        Route::delete('/service-categories/{id}', [ServiceCategoryController::class, 'destroy']);
        Route::get('/service-categories-deleted', [ServiceCategoryController::class, 'indexDeleted']);
        Route::get('/service-categories-all',    [ServiceCategoryController::class, 'indexWithTrashed']);
        Route::put('/service-categories/{id}',   [ServiceCategoryController::class, 'update']);
        Route::delete('/service-categories/{id}', [ServiceCategoryController::class, 'destroy']);
        Route::post('/service-categories/{id}/restore',     [ServiceCategoryController::class, 'restore']);
        Route::delete('/service-categories/{id}/force',     [ServiceCategoryController::class, 'forceDelete']);
        ######################### End Service Categories #########################

        ######################### Services #########################
        Route::post('/services',          [ServiceController::class, 'store']);
        Route::put('/services/{id}',      [ServiceController::class, 'update']);
        Route::delete('/services/{id}',   [ServiceController::class, 'destroy']);

        Route::get('/services-deleted',   [ServiceController::class, 'indexDeleted']);
        Route::get('/services-all',       [ServiceController::class, 'indexWithTrashed']);
        Route::post('/services/{id}/restore',     [ServiceController::class, 'restore']);
        Route::delete('/services/{id}/force',     [ServiceController::class, 'forceDelete']);
        ######################### End Services #########################


        ######################### States #########################
        Route::post('/add_states',             [StateController::class, 'store']);
        Route::put('/states/{id}',         [StateController::class, 'update']);
        Route::delete('/states/{id}',      [StateController::class, 'destroy']);
        Route::get('/states-deleted',      [StateController::class, 'deleted']);
        Route::get('/states-all',          [StateController::class, 'allWithTrashed']);
        Route::post('/states/{id}/restore', [StateController::class, 'restore']);
        ######################### End States #########################

        ######################### Areas #########################
        Route::post('/add_areas',              [AreaController::class, 'store']);
        Route::put('/areas/{id}',          [AreaController::class, 'update']);
        Route::delete('/areas/{id}',       [AreaController::class, 'destroy']);
        Route::get('/areas-deleted',       [AreaController::class, 'deleted']);
        Route::get('/areas-all',           [AreaController::class, 'allWithTrashed']);
        Route::post('/areas/{id}/restore', [AreaController::class, 'restore']);
        ######################### End Areas #########################
    });
});
