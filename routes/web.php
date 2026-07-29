<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ProfileController,
    DashboardController,
    EventController,
    MpesaController,
    SliderController,
    CartController,
    BudgetController,
    ReportController,
    AttendeeController,
    TicketController,
    QrCodeController,
};
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Auth\GoogleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Register web routes for the application.
| Frontend and backend routes are grouped for better management.
|
*/

// Add this right after the callback route
Route::get('/api/debug', function() {
    return response()->json([
        'csrf_token' => csrf_token(),
        'session_id' => session()->getId(),
        'all_headers' => request()->headers->all(),
    ]);
});


// ============================================
// PUBLIC ROUTES (No authentication required)
// ============================================

// Frontend Routes
Route::get('/', [EventController::class, 'frontendIndex'])->name('home');
Route::get('/events', [EventController::class, 'frontendIndex'])->name('events.index');
Route::get('/event/{id}', [EventController::class, 'publicShow'])->name('event.show');

// Slider Routes
Route::get('/slider', [SliderController::class, 'index'])->name('slider.index');

// Terms and Conditions
Route::get('/terms', fn() => view('terms'))->name('terms');

// Google Authentication
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// ============================================
// AUTHENTICATED ROUTES (Requires login)
// ============================================

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    // ============================================
    // USER PROFILE ROUTES
    // ============================================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
        Route::delete('/avatar', [ProfileController::class, 'deleteAvatar'])->name('delete-avatar');
        Route::put('/address', [ProfileController::class, 'updateAddress'])->name('address.update');
        Route::get('/data', [ProfileController::class, 'getUserData'])->name('data');
    });

    // ============================================
    // STATIC PAGES
    // ============================================
    Route::view('/index', 'index')->name('index');
    Route::view('/invoice', 'invoice')->name('invoice');
    Route::view('/404', '404')->name('404');
    Route::view('/messages', 'messages')->name('messages');
    Route::view('/alerts', 'alerts')->name('alerts');
    Route::view('/blank', 'blank')->name('blank');
    Route::view('/calendar', 'calendar')->name('calendar');
    Route::view('/form-elements', 'form-elements')->name('form-elements');
    Route::view('/basic-tables', 'basic-tables')->name('basic-tables');
    Route::view('/avatars', 'avatars')->name('avatars');
    Route::view('/badge', 'badge')->name('badge');
    Route::view('/buttons', 'buttons')->name('buttons');
    Route::view('/images', 'images')->name('images');
    Route::view('/videos', 'videos')->name('videos');
    Route::view('/signin', 'signin')->name('signin');
    Route::view('/signup', 'signup')->name('signup');
    Route::view('/image', 'image')->name('image');
    Route::view('/line-chart', 'line-chart')->name('line-chart');
    Route::view('/bar-chart', 'bar-chart')->name('bar-chart');
    Route::view('/dash', 'dash')->name('dash');

    // ============================================
    // BACKEND EVENT MANAGEMENT ROUTES (Auth Required)
    // ============================================
        
    Route::get('/events', [EventController::class, 'userEvents'])->name('events.index');
    Route::get('/events/past', [EventController::class, 'pastUserEvents'])->name('events.past');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{id}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{id}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{id}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::get('/events/{id}', [EventController::class, 'eventPage'])->name('events.show');

    // ============================================
    // TICKET ROUTES (Nested under events)
    // ============================================
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::put('/tickets/{id}', [TicketController::class, 'update'])->name('tickets.update');
    Route::delete('/tickets/{id}', [TicketController::class, 'destroy'])->name('tickets.destroy');
    
    Route::prefix('events/{event_id}/tickets')->name('tickets.')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('index');
        Route::get('/create', [TicketController::class, 'create'])->name('create');
        Route::get('/{id}/edit', [TicketController::class, 'edit'])->name('edit');
    });

    // ============================================
    // ATTENDEE ROUTES
    // ============================================
    Route::prefix('attendees')->name('attendees.')->group(function () {
        Route::get('/tickets/{ticket_id}', [AttendeeController::class, 'index'])->name('index');
        Route::get('/events/{event_id}', [AttendeeController::class, 'index'])->name('event.index');
    });

    // ============================================
    // QR CODE ROUTES
    // ============================================
    Route::prefix('qrcode')->name('qrcode.')->group(function () {
        Route::get('/', [QrCodeController::class, 'index'])->name('index');
        Route::get('/{event_id}', [QrCodeController::class, 'showQrCodePage'])->name('show');
        Route::get('/scan/{id}', [QrCodeController::class, 'scanQrCode'])->name('scan');
    });

    // ============================================
    // REPORT ROUTES
    // ============================================
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');

    // ============================================
    // BUDGET ROUTES
    // ============================================
    Route::resource('budgets', BudgetController::class)->parameters([
        'budgets' => 'id'
    ]);

    // ============================================
    // SYSTEM SETTINGS ROUTES (Admin only)
    // ============================================
    Route::prefix('system')->name('system.')->middleware(['can:admin'])->group(function () {
        Route::get('/', [SystemController::class, 'index'])->name('index');
        Route::put('/update', [SystemController::class, 'update'])->name('update');
        Route::get('/clear-cache', [SystemController::class, 'clearCache'])->name('clear-cache');
        Route::get('/backup', [SystemController::class, 'backupDatabase'])->name('backup');
        Route::post('/toggle-maintenance', [SystemController::class, 'toggleMaintenance'])->name('toggle-maintenance');
        Route::post('/debug', [SystemController::class, 'debug'])->name('debug');
    });
});

// ============================================
// CART ROUTES (Public)
// ============================================
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'viewCart'])->name('view');
    Route::get('/thankyou', [CartController::class, 'thankyou'])->name('thankyou');
    Route::post('/add', [CartController::class, 'addToCart'])->name('add');
    Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::get('/clear', [CartController::class, 'clearCart'])->name('clear');
    Route::post('/remove/{ticket_id}', [CartController::class, 'removeTicket'])->name('remove');
});

// ============================================
// MPESA PAYMENT ROUTES (All routes here)
// ============================================

// M-Pesa Web Routes (for your app)
Route::prefix('mpesa')->name('mpesa.')->group(function () {
    Route::post('/stkpush', [MpesaController::class, 'stkPush'])->name('stkpush');
    Route::get('/status/{checkoutRequestId}', [MpesaController::class, 'checkTransactionStatus'])->name('status');
});


// Test routes for debugging
Route::get('/api/test-mpesa', function() {
    return response()->json([
        'environment' => config('mpesa.environment'),
        'consumer_key' => config('mpesa.mpesa_consumer_key') ? 'Set' : 'Not Set',
        'consumer_secret' => config('mpesa.mpesa_consumer_secret') ? 'Set' : 'Not Set',
        'shortcode' => config('mpesa.shortcode'),
        'passkey' => config('mpesa.passkey') ? 'Set' : 'Not Set',
        'callback_url' => config('mpesa.callback_url'),
        'routes' => [
            'pay' => route('api.mpesa.callback', [], false),
            'confirmation' => route('api.mpesa.confirmation', [], false),
            'validation' => route('api.mpesa.validation', [], false),
        ]
    ]);
});

Route::post('/api/test-callback', function(Request $request) {
    Log::info('🔔 TEST CALLBACK RECEIVED', ['data' => $request->all()]);
    return response()->json([
        'success' => true,
        'message' => 'Test callback received!',
        'data' => $request->all()
    ]);
});

// ============================================
// TICKET PDF GENERATION ROUTES
// ============================================
Route::get('/events/{event_id}/tickets/{ticket_id}/attendees/{attendee_id}/generate-pdf', 
    [TicketController::class, 'generateTicketPDF']
)->name('tickets.generatePDF');

Route::get('/ticket/pdf/{attendeeId}', 
    [TicketController::class, 'generateTicketPDF']
)->name('ticket.pdf');

Route::post('/api/pay', [MpesaController::class, 'handleCallback'])
    ->name('api.mpesa.callback')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);





// ============================================
// AUTHENTICATION ROUTES
// ============================================
require __DIR__ . '/auth.php';