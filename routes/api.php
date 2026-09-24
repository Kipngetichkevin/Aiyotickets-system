<?php

use App\Http\Controllers\MpesaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// M-Pesa API routes (CSRF excluded via VerifyCsrfToken)
Route::post('/stkpush',      [MpesaController::class, 'stkPush']);
Route::post('/pay',          [MpesaController::class, 'handleCallback']);
Route::post('/confirmation', [MpesaController::class, 'handleConfirmation']);
Route::post('/validation',   [MpesaController::class, 'handleValidation']);

// Frontend polling
Route::get('/mpesa/status/{checkoutRequestId}', [MpesaController::class, 'checkTransactionStatus']);
Route::get('/mpesa/query/{checkoutRequestId}',  [MpesaController::class, 'queryTransaction']);