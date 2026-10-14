{{-- Buyer emails: how to reach their account (guest buyers get one automatically, by email) --}}
@php($account = \App\Models\User::find($order->user_id))
@if ($account)

---

**Your {{ $appName }} account**

@if ($account->hasVerifiedEmail())
Log in with **{{ $account->email }}** any time to see all your tickets, QR codes and balances: [open my account]({{ route('login', ['redirect' => '/my-qrs']) }}).
@else
Your tickets are also kept in an account for **{{ $account->email }}**. To see everything in one place — tickets, QR codes and any balance — [set a password]({{ route('password.request', ['email' => $account->email]) }}) and log in.
@endif
@endif
