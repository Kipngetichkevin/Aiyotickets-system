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
    FinanceController,
    OrderController,
    MyTicketController,
    MyOrderController,
};
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\MpesaCallbackController;
use App\Http\Controllers\Auth\GoogleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ============================================
// PUBLIC ROUTES (No authentication required)
// ============================================

Route::get('/', [EventController::class, 'frontendIndex'])->name('home');
Route::get('/browse', [EventController::class, 'browse'])->name('browse');
Route::get('/event/{id}', [EventController::class, 'publicShow'])->name('event.show');

Route::get('/slider', [SliderController::class, 'index'])->name('slider.index');

Route::get('/terms', fn() => view('terms'))->name('terms');

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// ============================================
// AUTHENTICATED ROUTES (Requires login)
// ============================================

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    // ── Profile ─────────────────────────────────────────
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
        Route::delete('/avatar', [ProfileController::class, 'deleteAvatar'])->name('delete-avatar');
        Route::put('/address', [ProfileController::class, 'updateAddress'])->name('address.update');
        Route::get('/data', [ProfileController::class, 'getUserData'])->name('data');
    });

    // ── Buyer ───────────────────────────────────────────
    Route::get('/my-tickets', [MyTicketController::class, 'index'])->name('my-tickets.index');
    Route::get('/my-orders',  [MyOrderController::class, 'index'])->name('my-orders.index');

    // ── Events ──────────────────────────────────────────
    Route::get('/events', [EventController::class, 'userEvents'])->name('events.index');
    Route::get('/events/past', [EventController::class, 'pastUserEvents'])->name('events.past');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{id}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{id}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{id}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::get('/events/{id}', [EventController::class, 'eventPage'])->name('events.show');

    // ── Tickets ─────────────────────────────────────────
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::put('/tickets/{id}', [TicketController::class, 'update'])->name('tickets.update');
    Route::delete('/tickets/{id}', [TicketController::class, 'destroy'])->name('tickets.destroy');
    Route::get('/tickets', [TicketController::class, 'allTickets'])->name('tickets.all');

    Route::prefix('events/{event_id}/tickets')->name('tickets.')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('index');
        Route::get('/create', [TicketController::class, 'create'])->name('create');
        Route::get('/{id}/edit', [TicketController::class, 'edit'])->name('edit');
    });

    // ── Attendees ───────────────────────────────────────
    Route::prefix('attendees')->name('attendees.')->group(function () {
        Route::get('/', [AttendeeController::class, 'allAttendees'])->name('all');
        Route::get('/tickets/{ticket_id}', [AttendeeController::class, 'index'])->name('index');
        Route::get('/events/{event_id}', [AttendeeController::class, 'index'])->name('event.index');
    });

    // ── QR codes ────────────────────────────────────────
    Route::prefix('qrcode')->name('qrcode.')->group(function () {
        Route::get('/', [QrCodeController::class, 'index'])->name('index');
        Route::get('/{event_id}', [QrCodeController::class, 'showQrCodePage'])->name('show');
        Route::get('/scan/{id}', [QrCodeController::class, 'scanQrCode'])->name('scan');
    });

    Route::get('/reports', [ReportController::class, 'index'])->name('reports');

    Route::resource('budgets', BudgetController::class)->parameters([
        'budgets' => 'id'
    ]);

    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/', [FinanceController::class, 'index'])->name('index');
        Route::post('/expenses', [FinanceController::class, 'storeExpense'])->name('expenses.store');
        Route::put('/expenses/{id}', [FinanceController::class, 'updateExpense'])->name('expenses.update');
        Route::delete('/expenses/{id}', [FinanceController::class, 'destroyExpense'])->name('expenses.destroy');
        Route::post('/suppliers', [FinanceController::class, 'storeSupplier'])->name('suppliers.store');
        Route::delete('/suppliers/{id}', [FinanceController::class, 'destroySupplier'])->name('suppliers.destroy');
    });

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{id}', [OrderController::class, 'show'])->name('show');
        Route::post('/{id}/invoice', [OrderController::class, 'generateInvoice'])->name('generate-invoice');
    });

    // ── System settings (Admin only) ────────────────────
    Route::prefix('system')->name('system.')->middleware(['can:admin'])->group(function () {
        Route::get('/', [SystemController::class, 'index'])->name('index');
        Route::put('/update', [SystemController::class, 'update'])->name('update');
        Route::get('/clear-cache', [SystemController::class, 'clearCache'])->name('clear-cache');
        Route::post('/toggle-maintenance', [SystemController::class, 'toggleMaintenance'])->name('toggle-maintenance');
    });

    // ── M-Pesa callbacks audit (Admin only) ─────────────
    Route::prefix('admin')->name('admin.')->middleware(['can:admin'])->group(function () {
        Route::get('/mpesa-callbacks', [MpesaCallbackController::class, 'index'])
            ->name('mpesa-callbacks.index');
        Route::get('/mpesa-callbacks/{callback}', [MpesaCallbackController::class, 'show'])
            ->name('mpesa-callbacks.show');
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
    Route::post('/create-order', [CartController::class, 'createOrderAfterPayment'])->name('create-order');
    Route::get('/clear', [CartController::class, 'clearCart'])->name('clear');
    Route::post('/remove/{ticket_id}', [CartController::class, 'removeTicket'])->name('remove');
});

// ============================================
// MPESA PAYMENT ROUTES
// ============================================
Route::prefix('mpesa')->name('mpesa.')->group(function () {
    Route::post('/stkpush', [MpesaController::class, 'stkPush'])->name('stkpush');
    Route::get('/status/{checkoutRequestId}', [MpesaController::class, 'checkTransactionStatus'])->name('status');
    Route::get('/query/{checkoutRequestId}', [MpesaController::class, 'queryTransaction'])->name('query');
});

// ============================================
// TEST ROUTES
// ============================================
Route::get('/api/test-mpesa', function() {
    return response()->json([
        'environment'      => config('mpesa.environment'),
        'consumer_key'     => config('mpesa.consumer_key')     ? 'Set' : 'Not Set',
        'consumer_secret'  => config('mpesa.consumer_secret')  ? 'Set' : 'Not Set',
        'shortcode'        => config('mpesa.shortcode'),
        'passkey'          => config('mpesa.passkey')          ? 'Set' : 'Not Set',
        'callback_url'     => config('mpesa.callback_url'),
    ]);
});

Route::get('/test-callback-url', function() {
    return response()->json([
        'status'    => 'success',
        'message'   => 'Callback URL is accessible',
        'timestamp' => now()->toDateTimeString(),
        'ip'        => request()->ip(),
    ]);
});

// ============================================
// TICKET PDF GENERATION ROUTES
// ============================================
Route::get('/events/{event_id}/tickets/{ticket_id}/attendees/{attendee_id}/generate-pdf',
    [TicketController::class, 'generatePDF']
)->name('tickets.generatePDF');

Route::get('/ticket/pdf/{attendeeId}',
    [TicketController::class, 'generatePDF']
)->name('ticket.pdf');

// ============================================
// AUTHENTICATION ROUTES
// ============================================
require __DIR__ . '/auth.php';