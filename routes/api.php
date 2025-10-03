<?php

use App\Http\Controllers\MainControllers\RatingController;
use App\Http\Controllers\MainControllers\AreaController;
use App\Http\Controllers\MainControllers\CouponController;
use App\Http\Controllers\MainControllers\OrderServiceController;
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



Route::middleware('auth:api')->group(function () {

     // إنشاء/تحديث تقييم لطلب
    Route::post('/orders/{order}/rate', [RatingController::class, 'storeOrUpdate']);

    // إظهار تقييم الطلب (إن وُجد)
    Route::get('/orders/{order}/rating', [RatingController::class, 'showForOrder']);

    // جميع تقييمات فنّي
    Route::get('/technicians/{technician}/ratings', [RatingController::class, 'listForTechnician']);

    // ملخّص تقييمات فنّي (المعدّل + العدد)
    Route::get('/technicians/{technician}/rating-summary', [RatingController::class, 'summaryForTechnician']);





    // عرض (للكل)
    Route::get('/service-categories', [ServiceCategoryController::class, 'index']);
    Route::get('/service-categories/{id}', [ServiceCategoryController::class, 'show']);

    Route::get('/services',               [ServiceController::class, 'index']);
    Route::get('/services/{id}',          [ServiceController::class, 'show']);

    Route::get('/get_states',              [StateController::class, 'index']);

    Route::get('/get_areas',               [AreaController::class, 'index']);
    Route::get('/states/{stateId}/areas', [AreaController::class, 'byState']); // مناطق محافظة
    Route::get('/get_coupons',            [CouponController::class, 'index']);
    // تطبيق كوبون على طلب
    Route::post('/coupons/apply',     [CouponController::class, 'apply']);



      // Orders: list & show
    Route::get('/orders',        [OrderServiceController::class, 'index']);
    Route::get('/orders/{id}',   [OrderServiceController::class, 'show']);

    // Customer: create order
    Route::post('/add_order',       [OrderServiceController::class, 'store']);

    // Upload media (image/video) to an order (customer/assigned tech/admin)
    Route::post('/orders/{id}/media', [OrderServiceController::class, 'addMedia']);

    // Technician: submit quote
    Route::post('/edit_order_by_technician', [OrderServiceController::class, 'technicianQuote']);

    // Admin: estimate/assign/final price/update status
    Route::post('/orders/{id}/estimate',    [OrderServiceController::class, 'adminEstimate']);      // admin initial price
    Route::post('/orders/{id}/assign',      [OrderServiceController::class, 'assignTechnician']);   // assign tech
    Route::post('/orders/{id}/final-price', [OrderServiceController::class, 'setFinalPrice']);      // set final price => awaiting_customer_approval
    Route::post('/orders/{id}/status',      [OrderServiceController::class, 'updateStatus']);       // generic status change (admin)

    // Customer: approve / reject
    Route::post('/orders/{id}/approve', [OrderServiceController::class, 'approve']);
    Route::post('/orders/{id}/complete', [OrderServiceController::class, 'completeByTechnician']);
    Route::post('/orders/{id}/reject',  [OrderServiceController::class, 'reject']);

    // Soft delete / trash / restore
    Route::delete('/orders/{id}',       [OrderServiceController::class, 'destroy']);
    Route::get('/orders-deleted',       [OrderServiceController::class, 'deleted']);
    Route::post('/orders/{id}/restore', [OrderServiceController::class, 'restore']);



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

        ######################### Coupons #########################

        Route::post('/add_coupons',           [CouponController::class, 'store']);
        Route::put('/coupons/{id}',       [CouponController::class, 'update']);
        Route::delete('/coupons/{id}',    [CouponController::class, 'destroy']);

        Route::get('/coupons-deleted',    [CouponController::class, 'deleted']);
        Route::get('/coupons-all',        [CouponController::class, 'allWithTrashed']);
        Route::post('/coupons/{id}/restore', [CouponController::class, 'restore']);
        ######################### End Coupons #####################

        ######################### Orders #########################
        Route::post('/orders/{id}/estimate', [OrderServiceController::class, 'adminEstimate']);         // وضع سعر ابتدائي
        Route::post('/orders/{id}/assign',   [OrderServiceController::class, 'assignTechnician']);      // تعيين فني
        Route::post('/orders/{id}/status',   [OrderServiceController::class, 'updateStatus']);          // تحديث الحالة
        Route::delete('/orders/{id}',        [OrderServiceController::class, 'destroy']);               // Soft Delete
        Route::get('/orders-deleted',        [OrderServiceController::class, 'deleted']);               // المحذوفات فقط
        Route::post('/orders/{id}/restore',  [OrderServiceController::class, 'restore']);               // استرجاع
        ######################### End Orders #########################

    });
});
