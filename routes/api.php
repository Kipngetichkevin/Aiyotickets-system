<?php

use App\Http\Controllers\MpesaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// M-Pesa Routes
// Route::post('/stkpush', [MpesaController::class, 'stkPush'])->name('mpesa.stkpush');
Route::post('/pay', [MpesaController::class, 'handleCallback'])->name('mpesa.callback');
Route::post('/confirmation', [MpesaController::class, 'handleConfirmation'])->name('mpesa.confirmation');
Route::post('/validation', [MpesaController::class, 'handleValidation'])->name('mpesa.validation');

// Test M-Pesa Configuration Route - Disabled in production for security
Route::get('/test-mpesa', function() {
    // Only allow in non-production environments
    if (config('app.env') === 'production') {
        return response()->json(['message' => 'Test endpoint disabled in production'], 403);
    }
    
    $config = [
        'environment' => config('mpesa.environment'),
        'consumer_key' => config('mpesa.mpesa_consumer_key') ? 'Set' : 'Not Set',
        'consumer_secret' => config('mpesa.mpesa_consumer_secret') ? 'Set' : 'Not Set',
        'shortcode' => config('mpesa.shortcode'),
        'passkey' => config('mpesa.passkey') ? 'Set' : 'Not Set',
        'callback_url' => config('mpesa.callback_url'),
        'php_version' => phpversion(),
        'laravel_version' => app()->version(),
    ];
    
    return response()->json($config);
})->middleware('throttle:10,1'); // Rate limit to prevent abuse