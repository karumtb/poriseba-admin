<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\MasterController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\EnrolmentController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\DesignationController;
use App\Http\Controllers\Admin\DashboardController;

use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {

    // Admin only
    Route::middleware('admin')->group(function () {
        Route::prefix('vendor')->group(function () {
            Route::post('/save', [UserController::class, 'save']);
            Route::post('/listing', [UserController::class, 'listing']);
            Route::post('/view', [UserController::class, 'view']);
            Route::post('/remove', [UserController::class, 'delete']);
            Route::post('/search', [UserController::class, 'search']);
            Route::post('/status/toggle', [UserController::class, 'toogleStatus']);

        });

        Route::post('/subscription-plan/save', [SubscriptionPlanController::class, 'save']);
        Route::post('/subscription-plan/index', [SubscriptionPlanController::class, 'index']);
        Route::post('/subscription-plan/status/toggle', [SubscriptionPlanController::class, 'toogleStatus']);
        Route::post('/subscription-plan/remove', [SubscriptionPlanController::class, 'destroy']);
        Route::post('/placement-request', [EnrolmentController::class, 'placement_request']);
    });
    // Hospital Role Restricted Routes
    Route::middleware('role:admin')->group(function () {
    Route::post('/enrolment/bulk-approve', [EnrolmentController::class, 'bulkApprove']);
    Route::post('/hiring-request/approve', [EnrolmentController::class, 'bulkApproveHiring']);


    });

    Route::middleware('role:admin,vendor,hospital')->group(function () {
      Route::post('/enrolment/listing', [EnrolmentController::class, 'index']);
      Route::post('/designation/listing', [DesignationController::class, 'listing']);
      Route::post('/enrolment/view', [EnrolmentController::class, 'view']);


    });
     Route::middleware('role:admin,vendor')->group(function () {
        Route::post('/enrolment/save', [EnrolmentController::class, 'save']);
        Route::post('/enrolment/remove', [EnrolmentController::class, 'destroy']);
        Route::post('/enrolment/status/toggle', [EnrolmentController::class, 'toogleStatus']);
    });
    Route::middleware('role:hospital')->group(function () {
    Route::post('/enrolment/hiring', [EnrolmentController::class, 'bulkHiringRequest']);
    });

    // Authenticated users

    Route::post('/dashboard', [DashboardController::class, 'index']);

    Route::post('/update-profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});
