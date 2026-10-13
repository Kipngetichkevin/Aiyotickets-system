<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PromoCodeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('promo-codes.index', [
            'codes'  => PromoCode::with('event')->where('user_id', $user->id)->latest()->get(),
            'events' => Event::where('user_id', $user->id)->orderByDesc('date')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'code'        => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/'],
            'type'        => 'required|in:percent,fixed',
            'value'       => 'required|numeric|min:1' . ($request->input('type') === 'percent' ? '|max:100' : ''),
            'event_id'    => ['nullable', 'integer', Rule::exists('events', 'id')->where('user_id', $user->id)],
            'min_tickets' => 'nullable|integer|min:1|max:1000',
            'max_uses'    => 'nullable|integer|min:1',
            'starts_at'   => 'nullable|date',
            'ends_at'     => 'nullable|date|after:starts_at',
        ], ['code.regex' => 'Use letters, numbers, dashes or underscores only.']);

        $data['code'] = strtoupper($data['code']);
        if (PromoCode::where('code', $data['code'])->exists()) {
            return back()->withErrors(['code' => 'That code is already taken.'])->withInput();
        }

        PromoCode::create($data + ['user_id' => $user->id, 'min_tickets' => $data['min_tickets'] ?? 1]);

        return redirect()->route('promo-codes.index')->with('success', "Promo code {$data['code']} created.");
    }

    public function toggle(PromoCode $promoCode)
    {
        abort_unless((int) $promoCode->user_id === (int) Auth::id(), 403);

        $promoCode->update(['is_active' => !$promoCode->is_active]);

        return back()->with('success', "{$promoCode->code} " . ($promoCode->is_active ? 'activated' : 'deactivated') . '.');
    }
}
