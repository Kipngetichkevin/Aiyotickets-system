<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Experience;
use App\Services\Cancellations;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/** Organiser (or admin) cancels or postpones an event or experience; buyers are told and refunded. */
class ListingChangeController extends Controller
{
    public function __construct(private Cancellations $cancellations)
    {
    }

    public function cancel(Request $request, string $type, $id)
    {
        $item   = $this->owned($type, $id);
        $reason = $request->validate([
            'reason'  => 'required|string|max:500',
            'confirm' => 'accepted',
        ], ['confirm.accepted' => 'Tick the box to confirm you want to cancel and refund every buyer.'])['reason'];

        if ($item->cancelled_at) {
            return back()->with('error', "{$item->name} is already cancelled.");
        }

        $result = $this->cancellations->cancel($item, $reason);

        return back()->with('success', "{$item->name} is cancelled. {$result['places']} " . str('ticket')->plural($result['places'])
            . ' cancelled, KES ' . number_format($result['total']) . ' to refund across ' . count($result['orders']) . ' ' . str('order')->plural(count($result['orders']))
            . '. Buyers are being told by email' . (app(\App\Services\Messaging\AfricasTalkingSms::class)->enabled() ? ' and SMS' : '') . '.');
    }

    public function postpone(Request $request, string $type, $id)
    {
        $item = $this->owned($type, $id);
        $data = $request->validate([
            'start' => 'required|date|after:now',
            'end'   => 'nullable|date|after:start',
            'note'  => 'nullable|string|max:500',
        ]);

        if ($item->cancelled_at) {
            return back()->with('error', 'A cancelled listing can\'t be postponed.');
        }

        $notified = $this->cancellations->postpone($item, Carbon::parse($data['start']), isset($data['end']) ? Carbon::parse($data['end']) : null, $data['note'] ?? null);

        return back()->with('success', "{$item->name} moved to " . Carbon::parse($data['start'])->format('d M Y, H:i') . ". {$notified} " . str('buyer')->plural($notified) . ' told; they can ask for a refund for ' . \App\Support\RefundPolicy::POSTPONED_DAYS . ' days.');
    }

    private function owned(string $type, $id): Event|Experience
    {
        $item = match ($type) {
            'event'      => Event::find($id),
            'experience' => Experience::find($id),
            default      => null,
        };
        $user = auth()->user();

        abort_unless($item && ($user->hasRole('admin') || (int) $item->user_id === (int) $user->id), 403);

        return $item;
    }
}
