<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Experience;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Merchant side of lipa mdogo mdogo: switch instalments on per event/experience
 * and follow instalment orders.
 */
class InstalmentController extends Controller
{
    public function settings()
    {
        $user = Auth::user();

        return view('instalments.settings', [
            'events'      => Event::where('user_id', $user->id)->where('end_date', '>=', now())->orderBy('date')->get(),
            'experiences' => Experience::where('user_id', $user->id)
                ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
                ->orderBy('start_date')->get(),
            'orders'      => Order::where('payment_plan', 'instalments')->where('seller_id', $user->id)
                ->latest()->take(100)->get(),
        ]);
    }

    public function update(Request $request, string $type, int $id)
    {
        abort_unless(in_array($type, ['event', 'experience'], true), 404);

        $item = ($type === 'event' ? Event::class : Experience::class)::findOrFail($id);
        abort_unless((int) $item->user_id === (int) Auth::id(), 403);

        $data = $request->validate([
            'allow_instalments'          => 'nullable|boolean',
            'instalment_deposit_percent' => 'required|integer|min:10|max:90',
            'instalment_due_days'        => 'required|integer|min:1|max:90',
        ]);

        $item->update([
            'allow_instalments'          => $request->boolean('allow_instalments'),
            'instalment_deposit_percent' => $data['instalment_deposit_percent'],
            'instalment_due_days'        => $data['instalment_due_days'],
        ]);

        return back()->with('success', "Lipa mdogo mdogo " . ($item->allow_instalments ? 'on' : 'off') . " for {$item->name}.");
    }
}
