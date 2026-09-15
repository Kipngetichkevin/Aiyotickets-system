<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ProfileController,
    DashboardController,
    EventController,
    MpesaController,
    SliderController,
    CartController,
    ReportController,
    AttendeeController,
    TicketController,
    QrCodeController,
    PayeeController,
    ServiceController,
    PayoutController,
    CalendarController,
    DocumentationController,
};
use App\Http\Controllers\Auth\GoogleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Frontend Routes
Route::get('/', [EventController::class, 'index'])->name('home');
Route::get('/event/{id}', [EventController::class, 'show'])->name('event.show');
Route::get('/events', [EventController::class, 'index'])->name('events.index');

// Cart Routes
Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'viewCart'])->name('cart.view');
    Route::get('/thankyou', [CartController::class, 'thankyou'])->name('thankyou');
    Route::post('/add', [CartController::class, 'addToCart'])->name('cart.add');
    Route::post('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::get('/clear', [CartController::class, 'clearCart'])->name('cart.clear');
    Route::post('remove/{ticket_id}', [CartController::class, 'removeTicket'])->name('cart.remove');
});

// MPESA Payment Routes (C2B - receive money)
Route::prefix('mpesa')->name('mpesa.')->group(function () {
    Route::post('/stkpush', [MpesaController::class, 'stkPush'])->name('stkpush');
    Route::post('/callback', [MpesaController::class, 'handleCallback'])->name('callback');
    Route::get('/status/{checkoutRequestId}', [MpesaController::class, 'checkTransactionStatus'])->name('status');
});

// User Profile Routes
Route::middleware('auth')->prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Slider Routes
Route::get('/slider', [SliderController::class, 'index'])->name('slider.index');

// Terms and Conditions
Route::get('/terms', fn() => view('terms'))->name('terms');

// Google OAuth
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// ============================================================
// BACKEND ROUTES — protected by auth + admin middleware
// ============================================================
Route::middleware(['auth', 'admin'])->prefix('backend')->name('backend.')->group(function () {

    // QR Code
    Route::get('/qrcode', [QrCodeController::class, 'index'])->name('qrcode.index');
    Route::get('/qrcode/{event_id}', [QrCodeController::class, 'showQrCodePage'])->name('qrcode.show');
    Route::get('/qrcode/scan/{id}', [QrCodeController::class, 'scanQrCode'])->name('qrcode.scan');

    // Events
    Route::prefix('events')->name('events.')->group(function () {
        Route::get('/create', [EventController::class, 'create'])->name('create');
        Route::post('/store', [EventController::class, 'store'])->name('store');
        Route::get('/show', [EventController::class, 'userEvents'])->name('show');
        Route::get('/past', [EventController::class, 'pastUserEvents'])->name('past');
        Route::get('/edit/{event:id}', [EventController::class, 'edit'])->name('edit');
        Route::put('/update/{event:id}', [EventController::class, 'update'])->name('update');
        Route::get('/{event_id}/page', [EventController::class, 'eventPage'])->name('page');
        Route::delete('/delete/{event:id}', [EventController::class, 'deleteEvent'])->name('delete');
    });

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');

    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

    // Documentation
    Route::get('/documentation', [DocumentationController::class, 'index'])->name('documentation.index');

    // Attendees
    Route::get('tickets/{ticket_id}/attendees', [AttendeeController::class, 'index'])->name('attendees.index');
    Route::get('events/{event_id}/tickets', [AttendeeController::class, 'index'])->name('tickets.index');

    // Payees
    Route::resource('payees', PayeeController::class);

    // Services
    Route::resource('services', ServiceController::class);

    // Payouts
    Route::resource('payouts', PayoutController::class);
    Route::post('payouts/{payout}/approve', [PayoutController::class, 'approve'])
        ->name('payouts.approve');
    Route::post('payouts/{payout}/mark-paid', [PayoutController::class, 'markPaid'])
        ->name('payouts.mark-paid');
});

// ============================================================
// TICKET ROUTES — protected by auth + admin
// ============================================================
Route::middleware(['auth', 'admin'])->prefix('backend')->group(function () {
    Route::get('events/{event_id}/tickets', [TicketController::class, 'index'])
        ->name('tickets.index');
    Route::get('events/{event_id}/tickets/create', [TicketController::class, 'create'])
        ->name('tickets.create');
    Route::post('events/{event_id}/tickets', [TicketController::class, 'store'])
        ->name('tickets.store');
    Route::get('events/{event_id}/tickets/{ticket}', [TicketController::class, 'show'])
        ->name('tickets.show');
    Route::get('events/{event_id}/tickets/{ticket}/edit', [TicketController::class, 'edit'])
        ->name('tickets.edit');
    Route::put('events/{event_id}/tickets/{ticket}', [TicketController::class, 'update'])
        ->name('tickets.update');
    Route::delete('events/{event_id}/tickets/{ticket}', [TicketController::class, 'destroy'])
        ->name('tickets.destroy');
});

// ============================================================
// Dashboard (any logged-in user)
// ============================================================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
});

// Ticket PDF Routes
Route::get(
    'events/{event_id}/tickets/{ticket_id}/attendees/{attendee_id}/generate-pdf',
    [TicketController::class, 'generateTicketPDF']
)->name('tickets.generatePDF');
Route::get('/ticket/pdf/{attendeeId}', [TicketController::class, 'generateTicketPDF'])->name('ticket.pdf');

// Authentication Routes
require __DIR__ . '/auth.php';