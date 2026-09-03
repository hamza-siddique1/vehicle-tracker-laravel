<?php

use App\Http\Controllers\Ndtc\NdtcOrderController;
use App\Http\Controllers\Ndtc\NdtcWebhookController;
use App\Models\NdtcOrder;
use Illuminate\Support\Facades\Route;

// ── Authenticated NDTC routes ─────────────────────────────────
Route::prefix('ndtc')->name('ndtc.')->middleware(['auth'])->group(function () {

    // Orders
    Route::get('orders',                    [NdtcOrderController::class, 'index'])
         ->name('orders.index');
    Route::get('orders/create/{vehicleId}', [NdtcOrderController::class, 'create'])
         ->name('orders.create');
    Route::post('orders',                   [NdtcOrderController::class, 'store'])
         ->name('orders.store');
    Route::get('/{order}',            [NdtcOrderController::class, 'show'])
         ->name('orders.show');
    Route::get('orders/{order}/edit',       [NdtcOrderController::class, 'edit'])
         ->name('orders.edit');
    Route::put('orders/{order}',            [NdtcOrderController::class, 'update'])
         ->name('orders.update');
    Route::post('orders/{order}/finalize',  [NdtcOrderController::class, 'finalize'])
         ->name('orders.finalize');
    Route::post('orders/{order}/cancel',    [NdtcOrderController::class, 'cancel'])
         ->name('orders.cancel');

     // Status polling endpoint — used by detail page JS
     Route::get('orders/{order}/status', function (\App\Models\NdtcOrder $order) {
          return response()->json(['status' => $order->status]);
     })->name('orders.status');

    // Documents
    Route::post('orders/{order}/documents',
                [NdtcOrderController::class, 'storeDocument'])
         ->name('orders.documents.store');
    Route::post('orders/{order}/documents/{document}/replace',
                [NdtcOrderController::class, 'replaceDocument'])
         ->name('orders.documents.replace');
     Route::get('orders/{order}/documents/{document}/view', [NdtcOrderController::class, 'viewDocument'])
          ->name('orders.documents.view');

    // Test panel — dev only
    if (app()->environment('local')) {
        Route::get('test/webhook/{order}',
                   [\App\Http\Controllers\Ndtc\NdtcTestController::class, 'index'])
             ->name('test.webhook');
        Route::post('test/webhook/{order}/fire',
                    [\App\Http\Controllers\Ndtc\NdtcTestController::class, 'fire'])
             ->name('test.webhook.fire');
    }
});

// ── Public webhook — no auth, no CSRF ────────────────────────
Route::post('webhooks/ndtc', [NdtcWebhookController::class, 'handle'])
     ->name('ndtc.webhook')
     ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);


Route::delete('orders/{order}/archive', [NdtcOrderController::class, 'archive'])
    ->name('ndtc.orders.archive');
//
