<?php

use App\Http\Controllers\MpesaController;
use Illuminate\Support\Facades\Route;

// M-Pesa Callback Routes - NO SESSION, NO CSRF
Route::post('/pay', [MpesaController::class, 'handleCallback']);
Route::post('/confirmation', [MpesaController::class, 'handleConfirmation']);
Route::post('/validation', [MpesaController::class, 'handleValidation']);