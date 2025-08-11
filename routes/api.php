<?php

use App\Http\Controllers\MainControllers\OtpController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
