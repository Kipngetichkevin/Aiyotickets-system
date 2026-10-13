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
    ExperienceController,
    MyQrController,
};
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Auth\GoogleController;

// ============================================
// PUBLIC ROUTES
// ============================================
Route::get('/', [EventController::class, 'frontendIndex'])->name('home');
Route::get('/events/browse', [EventController::class, 'frontendIndex'])->name('events.browse');
Route::get('/event/{id}', [EventController::class, 'publicShow'])->name('event.show');

Route::get('/experience/{slug}', [ExperienceController::class, 'publicShow'])->name('experience.show');
Route::get('/experiences/browse', [ExperienceController::class, 'browse'])->name('experiences.browse');

Route::post('/experiences/{slug}/add-to-cart', [ExperienceController::class, 'addToCart'])
    ->name('experience.add-to-cart')
    ->middleware('throttle:30,1');

Route::get('/slider', [SliderController::class, 'index'])->name('slider.index');
Route::get('/terms', fn() => view('terms'))->name('terms');

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// ============================================
// AUTHENTICATED ROUTES
// ============================================
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    // ── My QR Codes (user side) ──
    Route::get('/my-qrs', [MyQrController::class, 'index'])->name('my-qrs');
    Route::get('/my-qrs/data', [MyQrController::class, 'json'])->name('my-qrs.json');

    // ── Experience Registration ──
    Route::post('/experiences/{slug}/register', [ExperienceController::class, 'register'])
        ->name('experience.register')
        ->middleware('throttle:5,1'); // sends an M-Pesa prompt

    // ── Profile ──
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
        Route::delete('/avatar', [ProfileController::class, 'deleteAvatar'])->name('delete-avatar');
        Route::put('/address', [ProfileController::class, 'updateAddress'])->name('address.update');
        Route::get('/data', [ProfileController::class, 'getUserData'])->name('data');
    });

    // ── Template demo pages (admin only) ──
    Route::middleware(['role:admin'])->group(function () {
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
    });

    // ── Events ──
    Route::get('/events', [EventController::class, 'userEvents'])->name('events.index');
    Route::get('/events/past', [EventController::class, 'pastUserEvents'])->name('events.past');

    Route::middleware(['role:merchant|admin'])->group(function () {
        Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
        Route::post('/events', [EventController::class, 'store'])->name('events.store');
        Route::get('/events/{id}/edit', [EventController::class, 'edit'])->name('events.edit');
        Route::put('/events/{id}', [EventController::class, 'update'])->name('events.update');
        Route::delete('/events/{id}', [EventController::class, 'destroy'])->name('events.destroy');
    });

    Route::get('/events/{id}', [EventController::class, 'eventPage'])->name('events.show');

    // ── All Bookings overview ──
    Route::get('/bookings', [ExperienceController::class, 'bookingsIndex'])
        ->name('bookings.index')
        ->middleware(['role:merchant|admin']);

    // ── Experiences (admin CRUD + bookings) ──
    Route::prefix('experiences')->name('experiences.')->middleware(['role:merchant|admin'])->group(function () {
        Route::get('/', [ExperienceController::class, 'index'])->name('index');
        Route::get('/create', [ExperienceController::class, 'create'])->name('create');
        Route::post('/', [ExperienceController::class, 'store'])->name('store');
        Route::get('/{id}/json', [ExperienceController::class, 'showJson'])->name('json');
        Route::get('/{id}/edit', [ExperienceController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ExperienceController::class, 'update'])->name('update');
        Route::delete('/{id}', [ExperienceController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [ExperienceController::class, 'toggleStatus'])->name('toggle-status');

        Route::get('/{id}/bookings', [ExperienceController::class, 'bookings'])->name('bookings');
        Route::get('/{id}/bookings/feed', [ExperienceController::class, 'bookingsFeed'])->name('bookings.feed');
        Route::post('/bookings/{booking}/check-in', [ExperienceController::class, 'toggleCheckIn'])->name('bookings.check-in');
    });

    // ── Tickets, Attendees, QR, Reports, Budgets ──
    Route::middleware(['role:merchant|admin'])->group(function () {
        Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
        Route::put('/tickets/{id}', [TicketController::class, 'update'])->name('tickets.update');
        Route::delete('/tickets/{id}', [TicketController::class, 'destroy'])->name('tickets.destroy');

        Route::prefix('events/{event_id}/tickets')->name('tickets.')->group(function () {
            Route::get('/', [TicketController::class, 'index'])->name('index');
            Route::get('/create', [TicketController::class, 'create'])->name('create');
            Route::get('/{id}/edit', [TicketController::class, 'edit'])->name('edit');
        });

        Route::prefix('attendees')->name('attendees.')->group(function () {
            Route::get('/tickets/{ticket_id}', [AttendeeController::class, 'index'])->name('index');
            Route::get('/events/{event_id}', [AttendeeController::class, 'index'])->name('event.index');
        });

        Route::prefix('qrcode')->name('qrcode.')->group(function () {
            Route::get('/', [QrCodeController::class, 'index'])->name('index');
            Route::get('/{event_id}', [QrCodeController::class, 'showQrCodePage'])->name('show');
            Route::post('/scan/{id}', [QrCodeController::class, 'scanQrCode'])->name('scan')->middleware('throttle:120,1');

            Route::get('/experience-scanner/{experience_id}', [QrCodeController::class, 'experienceScanner'])
                ->name('experience-scanner');
            Route::post('/experience-scanner/verify', [QrCodeController::class, 'verifyExperienceQr'])
                ->middleware('throttle:120,1')
                ->name('experience-scanner.verify');
        });

        Route::get('/reports', [ReportController::class, 'index'])->name('reports');

        Route::resource('budgets', BudgetController::class)->parameters(['budgets' => 'id']);
    });

    // ── System (admin) ──
    Route::prefix('system')->name('system.')->middleware(['role:admin'])->group(function () {
        Route::get('/', [SystemController::class, 'index'])->name('index');
        Route::put('/update', [SystemController::class, 'update'])->name('update');
        Route::get('/clear-cache', [SystemController::class, 'clearCache'])->name('clear-cache');
        Route::get('/backup', [SystemController::class, 'backupDatabase'])->name('backup');
        Route::post('/toggle-maintenance', [SystemController::class, 'toggleMaintenance'])->name('toggle-maintenance');
        Route::post('/debug', [SystemController::class, 'debug'])->name('debug');
    });
});

// ============================================
// CART
// ============================================
// Guests can build a cart; paying for it requires login.
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'viewCart'])->name('view');
    Route::post('/add', [CartController::class, 'addToCart'])->name('add')->middleware('throttle:30,1');
    Route::get('/clear', [CartController::class, 'clearCart'])->name('clear');
    Route::post('/remove/{ticket_id}', [CartController::class, 'removeTicket'])->name('remove');

    Route::middleware('auth')->group(function () {
        Route::get('/thankyou', [CartController::class, 'thankyou'])->name('thankyou');
        Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout')->middleware('throttle:5,1'); // sends an M-Pesa prompt
        Route::post('/create-order', [CartController::class, 'createOrderAfterPayment'])->name('create-order')->middleware('throttle:10,1');
    });
});

// ============================================
// MPESA
// ============================================
Route::prefix('mpesa')->name('mpesa.')->group(function () {
    Route::middleware('auth')->group(function () {
        Route::get('/status/{checkoutRequestId}', [MpesaController::class, 'checkTransactionStatus'])->name('status')->middleware('throttle:60,1');
        Route::get('/query/{checkoutRequestId}', [MpesaController::class, 'queryTransaction'])->name('query')->middleware('throttle:10,1');
    });

    // Safaricom server-to-server callbacks — must stay public.

    Route::post('/callback', [MpesaController::class, 'handleCallback'])
        ->name('callback')
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

    Route::post('/c2b/confirmation', [MpesaController::class, 'handleConfirmation'])
        ->name('c2b.confirmation')
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

    Route::post('/c2b/validation', [MpesaController::class, 'handleValidation'])
        ->name('c2b.validation')
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
});

// ============================================
// PDF
// ============================================
// The PDFs carry the entry QR code, so only the holder, the organiser or an admin may open them.
Route::middleware('auth')->group(function () {
    Route::get('/events/{event_id}/tickets/{ticket_id}/attendees/{attendee_id}/generate-pdf',
        [TicketController::class, 'generateTicketPDF'])->name('tickets.generatePDF');

    Route::get('/ticket/pdf/{attendee_id}',
        [TicketController::class, 'generateTicketPDF'])->name('ticket.pdf');

    Route::get('/experience/booking/{booking}/pdf',
        [ExperienceController::class, 'bookingPdf'])->name('experience.booking.pdf');
});

// ============================================
// AUTH
// ============================================
require __DIR__ . '/auth.php';