<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SliderController extends Controller
{
    /**
     * Featured events for the homepage hero slider.
     * - Only active, upcoming events with at least one ticket type.
     * - Ordered: soonest first, but events ending within 7 days get priority.
     * - Cached for 10 minutes to keep the homepage snappy.
     */
    public function index(Request $request)
    {
        $limit = (int) $request->get('limit', 6);
        $limit = max(1, min($limit, 12));

        $slides = Cache::remember("slider.events.{$limit}", now()->addMinutes(10), function () use ($limit) {
            $events = Event::query()
                ->where('status', 'active')
                ->whereDate('end_date', '>=', today())
                ->has('tickets')
                ->with([
                    'merchant:id,name',
                    'eventType:id,name,slug',
                    'tickets:id,event_id,price,quantity',
                ])
                ->orderByRaw('CASE WHEN date <= ? THEN 0 ELSE 1 END', [today()->addDays(7)])
                ->orderBy('date', 'ASC')
                ->limit($limit)
                ->get();

            return $events->map(function (Event $event) {
                $startingPrice = $event->tickets->min('price');
                $daysUntil     = Carbon::parse($event->date)->diffInDays(now(), false);

                return [
                    'id'             => $event->id,
                    'slug'           => $event->slug,
                    'name'           => $event->name,
                    'description'    => str((string) $event->description)
                                            ->stripTags()
                                            ->squish()
                                            ->limit(150)
                                            ->toString(),
                    'image'          => $event->banner ?: $event->image,
                    'fallback_image' => asset('images/grid-image/image-01.png'),
                    'date'           => optional($event->date)->toIso8601String(),
                    'formatted_date' => Carbon::parse($event->date)->format('D, d M Y'),
                    'time'           => Carbon::parse($event->date)->format('g:i A'),
                    'location'       => $event->location,
                    'event_type'     => $event->eventType?->name,
                    'event_type_slug'=> $event->eventType?->slug,
                    'merchant'       => $event->merchant?->name,
                    'url'            => route('event.show', $event->id),
                    'starting_price' => $startingPrice,
                    'price_label'    => $startingPrice ? 'From ' . $event->currency . ' ' . number_format($startingPrice, 0) : 'Free',
                    'urgency'        => $daysUntil >= -1 && $daysUntil <= 3
                                            ? 'Happening soon'
                                            : null,
                ];
            });
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'slides'  => $slides,
            ]);
        }

        return view('partials.slider', ['slides' => $slides]);
    }
}