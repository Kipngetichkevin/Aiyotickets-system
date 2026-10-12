<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use App\Models\ExperiencePackage;
use App\Models\ExperienceDay;
use App\Models\ExperienceActivity;
use App\Models\ExperienceCategory;
use App\Models\ExperienceBooking;
use App\Services\DarajaService;
use FPDF;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ExperienceController extends Controller
{
    /* =========================================================
     |  LIST (admin)
     |========================================================= */
    public function index(Request $request)
    {
        $user    = Auth::user();
        $isAdmin = $user->hasRole('admin');

        $query = Experience::with(['user', 'packages', 'categories'])
            ->withCount('bookings')
            ->orderByDesc('created_at');

        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $experiences = $query->paginate(12)->withQueryString();

        $statsQuery = Experience::query();
        if (!$isAdmin) {
            $statsQuery->where('user_id', $user->id);
        }

        $stats = [
            'total'    => (clone $statsQuery)->count(),
            'active'   => (clone $statsQuery)->where('status', 'active')->count(),
            'draft'    => (clone $statsQuery)->where('status', 'draft')->count(),
            'featured' => (clone $statsQuery)->where('featured', true)->count(),
        ];

        return view('experiences.index', compact('experiences', 'stats', 'isAdmin'));
    }

    /* =========================================================
     |  BROWSE (public)
     |========================================================= */
    public function browse(Request $request)
    {
        $query = Experience::with(['packages', 'categories'])
            ->where('status', 'active')
            ->orderByDesc('featured')
            ->orderByDesc('created_at');

        if ($request->filled('q')) {
            $s = $request->q;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('short_description', 'like', "%{$s}%")
                  ->orWhere('location', 'like', "%{$s}%");
            });
        }

        $experiences = $query->paginate(12)->withQueryString();

        return view('experience.browse', compact('experiences'));
    }

    /* =========================================================
     |  ALL BOOKINGS OVERVIEW (merchant/admin)
     |========================================================= */
    public function bookingsIndex(Request $request)
    {
        $user    = Auth::user();
        $isAdmin = $user->hasRole('admin');

        $query = Experience::with(['packages'])
            ->withCount('bookings')
            ->orderByDesc('created_at');

        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }

        $experiences = $query->get();

        $experiences->each(function ($exp) {
            $exp->checked_in_count = ExperienceBooking::where('experience_id', $exp->id)
                ->where('checked_in', true)->count();
            $exp->revenue = (float) ExperienceBooking::where('experience_id', $exp->id)
                ->where('payment_status', 'paid')->sum('total_price');
        });

        $totals = [
            'experiences' => $experiences->count(),
            'bookings'    => $experiences->sum('bookings_count'),
            'checked_in'  => $experiences->sum('checked_in_count'),
            'revenue'     => $experiences->sum('revenue'),
        ];

        return view('experiences.bookings-index', compact('experiences', 'totals', 'isAdmin'));
    }

    /* =========================================================
     |  JSON — prefill data for modal edit
     |========================================================= */
    public function showJson($id)
    {
        $experience = Experience::with([
            'packages',
            'days.activities',
            'categories',
        ])->findOrFail($id);

        $this->authorizeOwnership($experience);

        return response()->json([
            'success' => true,
            'data'    => $experience->toFormArray(),
        ]);
    }

    /* =========================================================
     |  STORE
     |========================================================= */
    public function store(Request $request)
    {
        $isAjax = $request->wantsJson() || $request->ajax() || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'name'              => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description'       => 'nullable|string',
            'location'          => 'nullable|string|max:255',
            'start_date'        => 'nullable|date',
            'end_date'          => 'nullable|date|after_or_equal:start_date',
            'total_capacity'    => 'nullable|integer|min:1',
            'status'            => 'required|in:active,draft,archived',
            'featured'          => 'nullable|boolean',
            'image'             => 'nullable|image|max:4096',
            'poster_image'      => 'nullable|image|max:4096',
            'banner'            => 'nullable|image|max:4096',

            'packages'                  => 'array',
            'packages.*.name'           => 'nullable|string|max:100',
            'packages.*.price'          => 'nullable|numeric|min:0',

            'days'                      => 'array',
            'days.*.day_number'         => 'nullable|integer',
            'days.*.title'              => 'nullable|string|max:255',
            'days.*.activities'         => 'array',
            'days.*.activities.*.title' => 'nullable|string|max:255',
            'days.*.activities.*.image' => 'nullable|image|max:4096',

            'categories'                => 'array',
            'categories.*.name'         => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $data = $validator->validated();

            if ($request->hasFile('image'))        $data['image']        = $request->file('image')->store('experiences/covers', 'public');
            if ($request->hasFile('poster_image')) $data['poster_image'] = $request->file('poster_image')->store('experiences/posters', 'public');
            if ($request->hasFile('banner'))       $data['banner']       = $request->file('banner')->store('experiences/banners', 'public');

            $data['user_id']     = Auth::id();
            $data['merchant_id'] = Auth::user()->merchant->id ?? null;
            $data['featured']    = $request->boolean('featured');

            $experience = Experience::create($data);

            $this->savePackages($experience, $request->input('packages', []));
            $this->saveDays($experience, $request->input('days', []), $request);
            $this->saveCategories($experience, $request->input('categories', []));

            DB::commit();

            if ($isAjax) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Experience created successfully.',
                    'redirect' => route('experiences.index'),
                    'data'     => ['id' => $experience->id, 'uuid' => $experience->uuid],
                ], 201);
            }

            return redirect()
                ->route('experiences.index')
                ->with('success', 'Experience created successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Experience create failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create experience: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Failed to create experience: ' . $e->getMessage())->withInput();
        }
    }

    /* =========================================================
     |  UPDATE
     |========================================================= */
    public function update(Request $request, $id)
    {
        $experience = Experience::findOrFail($id);
        $this->authorizeOwnership($experience);

        $isAjax = $request->wantsJson() || $request->ajax() || $request->expectsJson();

        $validator = Validator::make($request->all(), [
            'name'              => 'required|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description'       => 'nullable|string',
            'location'          => 'nullable|string|max:255',
            'start_date'        => 'nullable|date',
            'end_date'          => 'nullable|date|after_or_equal:start_date',
            'total_capacity'    => 'nullable|integer|min:1',
            'status'            => 'required|in:active,draft,archived',
            'featured'          => 'nullable|boolean',
            'image'             => 'nullable|image|max:4096',
            'poster_image'      => 'nullable|image|max:4096',
            'banner'            => 'nullable|image|max:4096',

            'days.*.activities.*.image' => 'nullable|image|max:4096',
        ]);

        if ($validator->fails()) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $data = $validator->validated();

            if ($request->hasFile('image')) {
                if ($experience->image) Storage::disk('public')->delete($experience->image);
                $data['image'] = $request->file('image')->store('experiences/covers', 'public');
            }
            if ($request->hasFile('poster_image')) {
                if ($experience->poster_image) Storage::disk('public')->delete($experience->poster_image);
                $data['poster_image'] = $request->file('poster_image')->store('experiences/posters', 'public');
            }
            if ($request->hasFile('banner')) {
                if ($experience->banner) Storage::disk('public')->delete($experience->banner);
                $data['banner'] = $request->file('banner')->store('experiences/banners', 'public');
            }

            $data['featured'] = $request->boolean('featured');
            $experience->update($data);

            $this->savePackages($experience, $request->input('packages', []));
            $this->saveDays($experience, $request->input('days', []), $request);
            $this->saveCategories($experience, $request->input('categories', []));

            DB::commit();

            if ($isAjax) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Experience updated successfully.',
                    'redirect' => route('experiences.index'),
                ]);
            }

            return redirect()
                ->route('experiences.index')
                ->with('success', 'Experience updated successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Experience update failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update experience: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Failed to update experience: ' . $e->getMessage())->withInput();
        }
    }

    /* =========================================================
     |  DELETE
     |========================================================= */
    public function destroy($id)
    {
        $experience = Experience::findOrFail($id);
        $this->authorizeOwnership($experience);

        foreach (['image', 'poster_image', 'banner'] as $field) {
            if ($experience->{$field}) {
                Storage::disk('public')->delete($experience->{$field});
            }
        }

        foreach ($experience->days as $day) {
            foreach ($day->activities as $act) {
                if ($act->image) Storage::disk('public')->delete($act->image);
            }
        }

        $experience->delete();

        return response()->json([
            'success' => true,
            'message' => 'Experience deleted successfully.',
        ]);
    }

    /* =========================================================
     |  TOGGLE STATUS
     |========================================================= */
    public function toggleStatus($id)
    {
        $experience = Experience::findOrFail($id);
        $this->authorizeOwnership($experience);

        $experience->status = $experience->status === 'active' ? 'draft' : 'active';
        $experience->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated.',
            'data'    => ['status' => $experience->status],
        ]);
    }

    /* =========================================================
     |  PUBLIC LANDING PAGE
     |========================================================= */
    public function publicShow($slug)
    {
        $experience = Experience::with([
            'packages'         => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'days'             => fn ($q) => $q->orderBy('day_number'),
            'days.activities'  => fn ($q) => $q->orderBy('sort_order'),
            'categories'       => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
        ])
        ->where('slug', $slug)
        ->where('status', 'active')
        ->firstOrFail();

        return view('experience.show', compact('experience'));
    }

    /* =========================================================
     |  REGISTER
     |========================================================= */
    public function register(Request $request, $slug)
    {
        $experience = Experience::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'package_id'  => 'required|exists:experience_packages,id',
            'category_id' => 'nullable|exists:experience_categories,id',
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|max:255',
            'phone'       => 'required|string|max:20',
            'gender'      => 'required|in:1,2,3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $package = ExperiencePackage::where('experience_id', $experience->id)
            ->where('is_active', true)
            ->findOrFail($request->package_id);

        $category = null;
        $categorySurcharge = 0;
        if ($request->filled('category_id')) {
            $category = ExperienceCategory::where('experience_id', $experience->id)
                ->where('is_active', true)
                ->find($request->category_id);
            if ($category) {
                $categorySurcharge = (float) $category->price_surcharge;
            }
        }

        $availablePkg = $package->availableSpots();
        if ($availablePkg !== null && $availablePkg <= 0) {
            return response()->json(['success' => false, 'message' => 'This package is sold out.'], 400);
        }
        if ($category) {
            $availableCat = $category->availableSpots();
            if ($availableCat !== null && $availableCat <= 0) {
                return response()->json(['success' => false, 'message' => 'This race category is sold out.'], 400);
            }
        }

        $phone = $this->normalizePhone($request->input('phone'));
        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid phone number. Use format 254XXXXXXXXX or 07XXXXXXXX.',
            ], 400);
        }

        $packagePrice = (float) $package->price;
        $total        = $packagePrice + $categorySurcharge;
        $ref          = 'EXP-' . strtoupper(Str::random(10));

        try {
            $daraja   = app(DarajaService::class);
            $response = $daraja->stkPush((int) round($total), $phone, $ref, 'Experience Registration');

            if (!isset($response['ResponseCode']) || (string) $response['ResponseCode'] !== '0') {
                return response()->json([
                    'success' => false,
                    'message' => $response['ResponseDescription'] ?? $response['errorMessage'] ?? 'STK push failed',
                ], 400);
            }
        } catch (\Throwable $e) {
            Log::error('Experience register STK error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        $pendingData = [
            'user_id'      => Auth::id(),
            'name'         => $request->input('name'),
            'email'        => $request->input('email'),
            'phone'        => $phone,
            'gender'       => $request->input('gender'),
            'payment_mode' => 'Mobile Money',

            'cart_items'   => [],
            'experience_items' => [
                [
                    'experience_id'      => $experience->id,
                    'package_id'         => $package->id,
                    'category_id'        => $category?->id,
                    'name'               => $experience->name . ' — ' . $package->name,
                    'qty'                => 1,
                    'package_price'      => $packagePrice,
                    'category_surcharge' => $categorySurcharge,
                    'price'              => $total,
                    'subtotal'           => $total,
                ],
            ],

            'total_amount'   => $total,
            'total_qty'      => 1,
            'event_id'       => null,
            'temp_order_ref' => $ref,
        ];

        $stk = \App\Models\MpesaStk::create([
            'merchant_request_id'  => $response['MerchantRequestID'] ?? null,
            'checkout_request_id'  => $response['CheckoutRequestID'],
            'response'             => $response,
            'pending_data'         => $pendingData,
            'user_id'              => Auth::id(),
            'result_code'          => '1',
            'result_desc'          => 'Awaiting payment confirmation',
            'amount'               => $total,
            'phone_number'         => $phone,
            'response_code'        => '0',
            'response_description' => 'Request sent to M-Pesa',
            'customer_message'     => 'Please check your phone to complete payment',
        ]);

        Log::info('Experience registration STK initiated', [
            'checkout_request_id' => $response['CheckoutRequestID'],
            'mpesa_stk_id'        => $stk->id,
            'experience_id'       => $experience->id,
            'amount'              => $total,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'STK push sent. Please check your phone.',
            'data'    => [
                'checkout_request_id' => $response['CheckoutRequestID'],
                'merchant_request_id' => $response['MerchantRequestID'] ?? null,
                'mpesa_stk_id'        => $stk->id,
                'amount'              => $total,
            ],
        ]);
    }

    /* =========================================================
     |  ADD TO CART
     |========================================================= */
    public function addToCart(Request $request, $slug)
    {
        $experience = Experience::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'package_id'  => 'required|exists:experience_packages,id',
            'category_id' => 'nullable|exists:experience_categories,id',
            'quantity'    => 'nullable|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $package = ExperiencePackage::where('experience_id', $experience->id)
            ->where('is_active', true)
            ->findOrFail($request->package_id);

        $category = null;
        $categorySurcharge = 0;
        if ($request->filled('category_id')) {
            $category = ExperienceCategory::where('experience_id', $experience->id)
                ->where('is_active', true)
                ->find($request->category_id);
            if ($category) {
                $categorySurcharge = (float) $category->price_surcharge;
            }
        }

        $qty            = (int) ($request->input('quantity', 1));
        $packagePrice   = (float) $package->price;
        $totalUnitPrice = $packagePrice + $categorySurcharge;

        $experiences = session()->get('experiences', []);

        $experiences[$experience->id] = [
            'experience_id'      => $experience->id,
            'package_id'         => $package->id,
            'category_id'        => $category?->id,
            'package_name'       => $package->name,
            'category_name'      => $category?->name,
            'event_id'           => null,
            'name'               => $experience->name . ' — ' . $package->name,
            'experience_name'    => $experience->name,
            'image'              => $experience->image,
            'price'              => $totalUnitPrice,
            'package_price'      => $packagePrice,
            'category_surcharge' => $categorySurcharge,
            'currency'           => $package->currency ?? 'KES',
            'quantity'           => $qty,
        ];

        session()->put('experiences', $experiences);

        return response()->json([
            'success'  => true,
            'message'  => 'Added to cart',
            'cart_url' => route('cart.view'),
        ]);
    }

    /* =========================================================
     |  BOOKINGS LIST (per experience)
     |========================================================= */
    public function bookings(Request $request, $id)
    {
        $experience = Experience::with(['packages', 'categories'])->findOrFail($id);
        $this->authorizeOwnership($experience);

        $query = ExperienceBooking::with(['package', 'category'])
            ->where('experience_id', $experience->id)
            ->orderByDesc('created_at');

        $filter = $request->input('filter', 'all');
        if ($filter === 'checked-in') {
            $query->where('checked_in', true);
        } elseif ($filter === 'not-checked-in') {
            $query->where('checked_in', false);
        } elseif ($filter === 'paid') {
            $query->where('payment_status', 'paid');
        }

        if ($request->filled('q')) {
            $s = $request->q;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('qr_code', 'like', "%{$s}%")
                  ->orWhere('transaction_code', 'like', "%{$s}%");
            });
        }

        $bookings = $query->paginate(25)->withQueryString();

        $stats = [
            'total'       => ExperienceBooking::where('experience_id', $experience->id)->count(),
            'paid'        => ExperienceBooking::where('experience_id', $experience->id)->where('payment_status', 'paid')->count(),
            'checked_in'  => ExperienceBooking::where('experience_id', $experience->id)->where('checked_in', true)->count(),
            'not_checked' => ExperienceBooking::where('experience_id', $experience->id)->where('checked_in', false)->count(),
            'revenue'     => (float) ExperienceBooking::where('experience_id', $experience->id)->where('payment_status', 'paid')->sum('total_price'),
        ];

        return view('experiences.bookings', compact('experience', 'bookings', 'stats', 'filter'));
    }

    /* =========================================================
     |  LIVE FEED — recently checked-in bookings
     |========================================================= */
    public function bookingsFeed($id)
    {
        $experience = Experience::findOrFail($id);
        $this->authorizeOwnership($experience);

        $items = ExperienceBooking::with(['package'])
            ->where('experience_id', $experience->id)
            ->where('checked_in', true)
            ->orderByDesc('checked_in_at')
            ->limit(20)
            ->get()
            ->map(function ($b) {
                return [
                    'id'       => $b->id,
                    'attendee' => $b->name,
                    'package'  => $b->package?->name,
                    'time'     => $b->checked_in_at?->format('H:i:s'),
                ];
            });

        return response()->json(['success' => true, 'items' => $items]);
    }

    /* =========================================================
     |  TOGGLE CHECK-IN
     |========================================================= */
    public function toggleCheckIn(Request $request, ExperienceBooking $booking)
    {
        if ($booking->experience) {
            $this->authorizeOwnership($booking->experience);
        }

        $booking->checked_in = ! $booking->checked_in;

        if ($booking->checked_in) {
            $booking->checked_in_at = now();
            $booking->checked_in_by = Auth::id();
        } else {
            $booking->checked_in_at = null;
            $booking->checked_in_by = null;
        }

        $booking->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'       => true,
                'checked_in'    => $booking->checked_in,
                'checked_in_at' => $booking->checked_in_at?->format('d M Y, H:i'),
            ]);
        }

        return back()->with('success', $booking->checked_in ? 'Marked as checked in.' : 'Check-in cleared.');
    }

    /* =========================================================
     |  EXPERIENCE BOOKING PDF
     |========================================================= */
    public function bookingPdf(ExperienceBooking $booking)
    {
        // Booking holder, the experience's owner, or an admin
        if ((int) $booking->user_id !== (int) Auth::id()) {
            abort_unless($booking->experience, 404, 'Experience not found for this booking.');
            $this->authorizeOwnership($booking->experience);
        }

        try {
            $booking->load(['experience', 'package', 'category']);

            $experience = $booking->experience;
            $package    = $booking->package;
            $category   = $booking->category;

            if (!$experience) {
                abort(404, 'Experience not found for this booking.');
            }

            $pdf = new FPDF('P', 'mm', [110, 220]);
            $pdf->AddPage();
            $pdf->SetMargins(10, 10, 10);

            $logoPath = public_path('images/logo/logo.png');

            if (file_exists($logoPath)) {
                $pdf->Image($logoPath, 20, -27.5, 70);
            } else {
                $pdf->SetFont('Arial', 'B', 22);
                $pdf->SetTextColor(50, 50, 50);
                $pdf->SetY(10);
                $pdf->Cell(0, 10, 'AIYO TICKETS', 0, 1, 'C');
            }

            $pdf->SetFont('Arial', 'B', 20);
            $pdf->SetTextColor(50, 50, 50);
            $pdf->SetY(50);
            $pdf->Cell(0, -65, 'Experience Ticket', 0, 1, 'C');

            $pdf->SetDrawColor(200, 200, 200);
            $pdf->Line(10, 25, 100, 25);

            $pdf->SetFont('Arial', 'B', 14);
            $pdf->SetY(25);
            $pdf->Cell(0, 10, $experience->name, 0, 1, 'C');

            $pdf->Line(10, 45, 100, 45);

            $pdf->SetFont('Arial', '', 11);
            if ($experience->location) {
                $pdf->Cell(0, 7, 'Location: ' . $experience->location, 0, 1, 'C');
            }

            $pdf->SetFont('Arial', 'B', 11);
            $start = $experience->start_date ? $experience->start_date->format('D, d M Y') : '—';
            $end   = $experience->end_date   ? $experience->end_date->format('D, d M Y')   : '—';
            $pdf->Cell(50, 7, $start, 0, 0, 'L');
            $pdf->Cell(0,  7, $end,   0, 1, 'R');

            $pdf->Line(10, 72.5, 100, 72.5);

            $pdf->Ln(8);
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, 'Package: ' . ($package->name ?? 'Standard'), 0, 1);

            $pdf->SetFont('Arial', '', 10);
            if ($package) {
                if ($package->transport_type) {
                    $pdf->Cell(0, 6, 'Transport: ' . $package->transport_type, 0, 1);
                }
                if ($package->accommodation_type) {
                    $pdf->Cell(0, 6, 'Accommodation: ' . $package->accommodation_type, 0, 1);
                }
                if ($package->accommodation_sharing) {
                    $pdf->Cell(0, 6, 'Sharing: ' . $package->accommodation_sharing, 0, 1);
                }
                $includes = is_array($package->includes) ? $package->includes : (json_decode($package->includes ?? '[]', true) ?: []);
                if (!empty($includes)) {
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->Cell(0, 6, 'Includes:', 0, 1);
                    $pdf->SetFont('Arial', '', 10);
                    foreach ($includes as $inc) {
                        $pdf->Cell(0, 5, '  - ' . $inc, 0, 1);
                    }
                }
            }

            if ($category) {
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(0, 6, 'Race Category: ' . $category->name, 0, 1);
            }

            $pdf->Ln(3);
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, 'Attendee Information', 0, 1);

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, $booking->name, 0, 1, 'L');

            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(0, 7, 'Total Paid: KES ' . number_format($booking->total_price, 2), 0, 1, 'L');
            if ($booking->transaction_code) {
                $pdf->Cell(0, 7, 'Receipt: ' . $booking->transaction_code, 0, 1, 'L');
            }

            $qrData = "AIYO TICKETS - Experience Booking\n"
                . "Experience: " . $experience->name . "\n"
                . "Package: " . ($package->name ?? '-') . "\n"
                . "Booking Ref: " . $booking->qr_code . "\n"
                . "Name: " . $booking->name . "\n"
                . "Total: KES " . number_format($booking->total_price, 2);

            $qrDir = public_path('storage/qr_codes');
            if (!file_exists($qrDir)) {
                mkdir($qrDir, 0755, true);
            }

            $qrCode = new QrCode($qrData);
            $writer = new PngWriter();
            $qrPath = $qrDir . '/exp_booking_' . $booking->id . '.png';

            file_put_contents($qrPath, $writer->write($qrCode)->getString());

            $pdf->Image($qrPath, 70, $pdf->GetY() - 25, 30, 30);

            $pdf->Ln(15);
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetTextColor(255, 0, 0);
            $pdf->Cell(0, 7, 'Do not share this ticket with anyone!', 0, 1, 'C');

            $pdf->Ln(15);
            $pdf->SetFont('Arial', 'I', 9);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->MultiCell(
                0,
                6,
                'This booking is valid for ' . $experience->name . '. By purchasing this experience you agree to the terms and conditions.',
                0,
                'J'
            );

            $pdf->SetFillColor(128, 0, 128);
            $pdf->Rect(0, 190, 110, 30, 'F');

            $pdf->SetY(189.5);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(0, 10, 'www.aiyotickets.com', 0, 1, 'C');

            if (file_exists($qrPath)) {
                unlink($qrPath);
            }

            $pdf->Output('D', 'experience-booking-' . $booking->id . '.pdf');

        } catch (\Throwable $e) {
            Log::error('Experience booking PDF generation failed', [
                'booking_id' => $booking->id ?? null,
                'message'    => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            abort(500, 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /* =========================================================
     |  PHONE HELPER
     |========================================================= */
    protected function normalizePhone(?string $raw): ?string
    {
        if (!$raw) return null;

        $digits = preg_replace('/\D/', '', $raw);

        if (strlen($digits) === 10 && $digits[0] === '0') {
            $digits = '254' . substr($digits, 1);
        } elseif (strlen($digits) === 9 && in_array($digits[0], ['7', '1'], true)) {
            $digits = '254' . $digits;
        }

        return preg_match('/^254\d{9}$/', $digits) ? $digits : null;
    }

    /* =========================================================
     |  SAVE HELPERS
     |========================================================= */
    protected function savePackages(Experience $experience, array $packages): void
    {
        $keep = [];
        foreach ($packages as $i => $p) {
            if (empty($p['name'])) continue;

            $row = $experience->packages()->updateOrCreate(
                ['id' => $p['id'] ?? null],
                [
                    'name'                  => $p['name'],
                    'tier'                  => $p['tier'] ?? ($i + 1),
                    'badge_color'           => $p['badge_color'] ?? null,
                    'tagline'               => $p['tagline'] ?? null,
                    'price'                 => $p['price'] ?? 0,
                    'currency'              => $p['currency'] ?? 'KES',
                    'transport_type'        => $p['transport_type'] ?? null,
                    'accommodation_type'    => $p['accommodation_type'] ?? null,
                    'accommodation_sharing' => $p['accommodation_sharing'] ?? null,
                    'includes'              => $this->normalizeTags($p['includes'] ?? []),
                    'max_capacity'          => $p['max_capacity'] ?? null,
                    'sort_order'            => $i,
                    'is_active'             => true,
                ]
            );
            $keep[] = $row->id;
        }
        $experience->packages()->whereNotIn('id', $keep)->delete();
    }

    protected function saveCategories(Experience $experience, array $categories): void
    {
        $keep = [];
        foreach ($categories as $i => $c) {
            if (empty($c['name'])) continue;

            $row = $experience->categories()->updateOrCreate(
                ['id' => $c['id'] ?? null],
                [
                    'name'            => $c['name'],
                    'distance_km'     => $c['distance_km'] ?? null,
                    'price_surcharge' => $c['price_surcharge'] ?? 0,
                    'max_capacity'    => $c['max_capacity'] ?? null,
                    'sort_order'      => $i,
                    'is_active'       => true,
                ]
            );
            $keep[] = $row->id;
        }
        $experience->categories()->whereNotIn('id', $keep)->delete();
    }

    protected function saveDays(Experience $experience, array $days, Request $request): void
    {
        $keepDays = [];
        $keepActs = [];

        foreach ($days as $i => $dayData) {
            if (empty($dayData['title'])) continue;

            $day = $experience->days()->updateOrCreate(
                ['id' => $dayData['id'] ?? null],
                [
                    'day_number'  => $dayData['day_number'] ?? ($i + 1),
                    'date'        => $dayData['date'] ?? null,
                    'title'       => $dayData['title'],
                    'description' => $dayData['description'] ?? null,
                    'sort_order'  => $i,
                ]
            );
            $keepDays[] = $day->id;

            foreach (($dayData['activities'] ?? []) as $j => $act) {
                if (empty($act['title'])) continue;

                $actData = [
                    'experience_id' => $experience->id,
                    'title'         => $act['title'],
                    'time_label'    => $act['time_label'] ?? null,
                    'description'   => $act['description'] ?? null,
                    'tags'          => $this->normalizeTags($act['tags'] ?? []),
                    'pro_tip'       => $act['pro_tip'] ?? null,
                    'sort_order'    => $j,
                ];

                $fileKey = "days.{$i}.activities.{$j}.image";
                if ($request->hasFile($fileKey)) {
                    if (!empty($act['id'])) {
                        $old = ExperienceActivity::find($act['id']);
                        if ($old && $old->image) {
                            Storage::disk('public')->delete($old->image);
                        }
                    }
                    $actData['image'] = $request->file($fileKey)->store('experiences/activities', 'public');
                }

                $row = $day->activities()->updateOrCreate(
                    ['id' => $act['id'] ?? null],
                    $actData
                );
                $keepActs[] = $row->id;
            }
        }

        $experience->activities()
            ->whereNotIn('id', $keepActs)
            ->each(function ($a) {
                if ($a->image) Storage::disk('public')->delete($a->image);
                $a->delete();
            });

        $experience->days()->whereNotIn('id', $keepDays)->delete();
    }

    /* =========================================================
     |  AUTHORIZATION
     |========================================================= */
    protected function authorizeOwnership(Experience $experience): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Not authenticated.');
        }

        if (!$user->hasRole('admin') && (int) $experience->user_id !== (int) $user->id) {
            abort(403, 'You do not own this experience.');
        }
    }

    /* =========================================================
     |  HELPERS
     |========================================================= */
    protected function normalizeTags($raw): array
    {
        if (is_array($raw)) {
            return array_values(array_filter(array_map('trim', $raw), fn ($t) => $t !== ''));
        }

        if (is_string($raw)) {
            return array_values(array_filter(array_map('trim', explode(',', $raw)), fn ($t) => $t !== ''));
        }

        return [];
    }
}