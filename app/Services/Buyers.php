<?php

namespace App\Services;

use App\Models\MpesaStk;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Guest checkout: buyers pay with name, phone and email, no login.
 *
 * Each purchase is linked to an account found (or created) by email, so tickets
 * are never orphaned; the buyer can claim that account later with "Forgot
 * password". A guest can follow their own payment and see their own order
 * because this browser session remembers them — nobody else can.
 */
class Buyers
{
    /** The logged-in user, or the account for this email (created if new). */
    public function resolve(string $name, string $email, ?string $phone = null): User
    {
        if ($user = auth()->user()) {
            return $user;
        }

        $email = Str::lower(trim($email));

        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'name'     => trim($name) ?: 'Customer',
                'email'    => $email,
                'phone'    => $phone,
                'password' => Hash::make(Str::random(40)),   // claimed later via "Forgot password"
            ]);
            $user->assignRole('user');
        }

        return $user;
    }

    /** Let this browser follow the payment (status polling, order creation). */
    public function rememberCheckout(string $checkoutRequestId): void
    {
        session()->push('guest_checkouts', $checkoutRequestId);
    }

    /** Let this browser see the order (thank-you page). */
    public function rememberOrder(Order $order): void
    {
        session()->push('guest_orders', $order->id);
    }

    public function canSeeCheckout(MpesaStk $stk): bool
    {
        $user = auth()->user();
        if ($user && ((int) $stk->user_id === (int) $user->id || $user->hasRole('admin'))) {
            return true;
        }

        return in_array($stk->checkout_request_id, session('guest_checkouts', []), true);
    }

    public function canSeeOrder(Order $order): bool
    {
        $user = auth()->user();
        if ($user && ((int) $order->user_id === (int) $user->id || $user->hasRole('admin'))) {
            return true;
        }

        return in_array($order->id, session('guest_orders', []), false);
    }
}
