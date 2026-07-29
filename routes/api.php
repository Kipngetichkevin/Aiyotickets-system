<?php

use App\Http\Controllers\MpesaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ============================================
// M-PESA API ROUTES (No CSRF protection)
// ============================================
Route::post('/stkpush', [MpesaController::class, 'stkPush'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::post('/pay', [MpesaController::class, 'handleCallback'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::post('/confirmation', [MpesaController::class, 'handleConfirmation'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::post('/validation', [MpesaController::class, 'handleValidation'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);