<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\ExperienceBooking;
use App\Models\Order;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\URL;

/**
 * Ticket pages reachable without logging in, through signed links sent by SMS/WhatsApp.
 * The signature makes the link unguessable and tamper-proof; it expires after 90 days.
 */
class TicketViewController extends Controller
{
    public const LINK_DAYS = 90;

    public static function linkFor(Order $order): string
    {
        // Signed on the path only, so the link works whatever host/port/proxy serves it
        return url(URL::temporarySignedRoute('tickets.view', now()->addDays(self::LINK_DAYS), ['order' => $order->uuid], absolute: false));
    }

    public static function qrLinkFor(string $code): string
    {
        return url(URL::temporarySignedRoute('tickets.qr', now()->addDays(self::LINK_DAYS), ['code' => $code], absolute: false));
    }

    public function show(Order $order)
    {
        $items = collect();

        foreach (Attendee::with('ticket.event')->where('order_id', $order->id)->whereIn('payment_status', ['paid', 'cancelled'])->get() as $a) {
            $items->push([
                'cancelled' => $a->payment_status === 'cancelled',
                'policy'   => $a->ticket?->event ? \App\Support\RefundPolicy::text($a->ticket->event) : null,
                'title'    => $a->ticket?->event?->name ?? 'Event',
                'subtitle' => $a->ticket?->name,
                'when'     => $a->ticket?->event?->date ? \Carbon\Carbon::parse($a->ticket->event->date)->format('D, d M Y · H:i') : null,
                'where'    => $a->ticket?->event?->location,
                'name'     => $a->name,
                'code'     => $a->ensureQrCode(),
                'checked'  => (bool) $a->checked_in,
            ]);
        }

        foreach (ExperienceBooking::with(['experience', 'package'])->where('order_id', $order->id)->whereIn('payment_status', ['paid', 'cancelled'])->get() as $b) {
            $items->push([
                'cancelled' => $b->payment_status === 'cancelled',
                'policy'   => $b->experience ? \App\Support\RefundPolicy::text($b->experience) : null,
                'title'    => $b->experience?->name ?? 'Experience',
                'subtitle' => $b->package?->name,
                'when'     => $b->experience?->start_date?->format('D, d M Y'),
                'where'    => $b->experience?->location,
                'name'     => $b->name,
                'code'     => $b->qr_code,
                'checked'  => (bool) $b->checked_in,
            ]);
        }

        // Cancelled tickets get no QR: they no longer work at the gate
        $items = $items->map(fn ($item) => $item + [
            'qr' => $item['cancelled'] || !$item['code'] ? null : 'data:image/png;base64,' . base64_encode((new PngWriter())->write(new QrCode($item['code']))->getString()),
        ]);

        $refundable = app(\App\Services\Cancellations::class)->refundablePlaces($order);
        $refundUrl  = $refundable->isNotEmpty()
            ? URL::temporarySignedRoute('tickets.refund', now()->addHour(), ['order' => $order->uuid], absolute: false)
            : null;

        return view('tickets.view', [
            'order'        => $order,
            'items'        => $items,
            'refundUrl'    => $refundUrl,
            'refundAmount' => (float) $refundable->sum('total_price'),
        ]);
    }

    /** Buyer asks for their money back, within the listing's refund policy. */
    public function requestRefund(Order $order, \App\Services\Cancellations $cancellations)
    {
        $result = $cancellations->requestRefund($order);

        return redirect(self::linkFor($order))->with($result['amount'] > 0 ? 'success' : 'error', $result['message']);
    }

    /** QR image for one code (used as the WhatsApp message image). */
    public function qr(string $code)
    {
        abort_unless(
            Attendee::where('qr_code', $code)->where('payment_status', 'paid')->exists()
            || ExperienceBooking::where('qr_code', $code)->where('payment_status', 'paid')->exists(),
            404
        );

        return response((new PngWriter())->write(new QrCode($code))->getString(), 200, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
