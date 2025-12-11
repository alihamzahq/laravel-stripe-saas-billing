<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PlanController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use App\Http\Controllers\Api\V1\PaymentMethodController;
use App\Http\Controllers\Api\V1\InvoiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::prefix('v1')->group(function () {
    // Auth routes (public)
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    // Auth routes (protected)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        // Subscriptions routes
        Route::prefix('subscriptions')->controller(SubscriptionController::class)->group(function () {
            Route::get('me', 'me');
            Route::post('/', 'store');
            Route::post('cancel', 'cancel');
            Route::post('resume', 'resume');
            Route::put('change-plan', 'changePlan');
        });

        // Payment Methods routes
        Route::apiResource('payment-methods', PaymentMethodController::class)->except(['show', 'update']);
        Route::post('payment-methods/set-default', [PaymentMethodController::class, 'setDefault']);

        // Setup Intent route
        Route::post('setup-intent', [PaymentMethodController::class, 'setupIntent']);

        // Invoices routes
        Route::apiResource('invoices', InvoiceController::class)->only(['index', 'show']);
        Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    });

    // Plans routes (public)
    Route::get('plans', [PlanController::class, 'index']);
    Route::get('plans/{plan}', [PlanController::class, 'show']);
});
