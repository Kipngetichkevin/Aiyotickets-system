<?php

namespace App\Http\Controllers;

use App\Mail\NewOrganiserApplication;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

/**
 * "Sell with us": anyone with a verified account applies to become an organiser.
 * An admin reviews the application (Admin\OrganiserController); approval grants the
 * merchant role, which is what unlocks creating events and experiences.
 */
class OrganiserController extends Controller
{
    /** The pitch for guests, the form for clients, the application's status once sent. */
    public function show(Request $request)
    {
        $user = $request->user();

        return view('organisers.apply', [
            'user'        => $user,
            'application' => $user?->merchant,
            'isOrganiser' => $user?->isStaff() ?? false,
        ]);
    }

    public function store(Request $request)
    {
        $user        = $request->user();
        $application = $user->merchant;

        if ($user->isStaff() || in_array($application?->status, [Merchant::APPROVED, Merchant::SUSPENDED], true)) {
            return redirect()->route('organisers.apply')->with('error', 'Your organiser account is already set up.');
        }

        $data = $request->validate([
            'name'                => 'required|string|max:120',
            'business_type'       => ['required', Rule::in(['individual', 'company'])],
            'id_number'           => 'required|string|max:50',
            'kra_pin'             => ['required', 'string', 'regex:/^[AP]\d{9}[A-Z]$/i'],
            'phone'               => ['required', 'string', 'regex:/^(?:\+?254|0)[17]\d{8}$/'],
            'email'               => 'required|email|max:255',
            'location'            => 'required|string|max:120',
            'website'             => 'nullable|url:http,https|max:255',
            'description'         => 'required|string|min:30|max:2000',
            'payout_method'       => ['required', Rule::in(['mpesa', 'bank'])],
            'payout_phone'        => ['required_if:payout_method,mpesa', 'nullable', 'regex:/^(?:\+?254|0)[17]\d{8}$/'],
            'bank_name'           => 'required_if:payout_method,bank|nullable|string|max:100',
            'bank_account_name'   => 'required_if:payout_method,bank|nullable|string|max:120',
            'bank_account_number' => 'required_if:payout_method,bank|nullable|string|max:50',
            'terms'               => 'accepted',
        ], [
            'kra_pin.regex'       => 'Enter a valid KRA PIN, e.g. A012345678Z.',
            'phone.regex'         => 'Enter a Kenyan mobile number, e.g. 0712345678.',
            'payout_phone.regex'  => 'Enter a Kenyan M-Pesa number, e.g. 0712345678.',
            'terms.accepted'      => 'Please accept the organiser terms.',
        ]);

        $data['kra_pin'] = strtoupper($data['kra_pin']);
        unset($data['terms']);

        $application ??= new Merchant(['user_id' => $user->id]);
        $application->fill($data);
        $application->forceFill([
            'status'      => Merchant::PENDING,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_note' => null,
        ])->save();

        $admins = User::role('admin')->pluck('email')->filter()->all();
        if ($admins) {
            Mail::to($admins)->queue(new NewOrganiserApplication($application));
        }

        return redirect()->route('organisers.apply')
            ->with('success', "Thanks! We'll review your application and email you, usually within one working day.");
    }
}
