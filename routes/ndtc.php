<?php

use App\Http\Controllers\Ndtc\NdtcOrderController;
use App\Http\Controllers\Ndtc\NdtcWebhookController;
use Illuminate\Support\Facades\Route;

// Authenticated routes
Route::prefix('ndtc')->name('ndtc.')->middleware(['auth'])->group(function () {
    Route::get('orders/create/{vehicleId}', [NdtcOrderController::class, 'create'])
         ->name('orders.create');
    Route::resource('orders', NdtcOrderController::class)
         ->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('orders/{order}/finalize', [NdtcOrderController::class, 'finalize'])
         ->name('orders.finalize');
    Route::post('orders/{order}/cancel', [NdtcOrderController::class, 'cancel'])
         ->name('orders.cancel');
});

// Public webhook — no auth, no CSRF
Route::post('webhooks/ndtc', [NdtcWebhookController::class, 'handle'])
     ->name('ndtc.webhook')
     ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
