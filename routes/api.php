<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');


use App\Http\Controllers\MainControllers\UserController;

Route::post('/user/register', [UserController::class, 'store']);
