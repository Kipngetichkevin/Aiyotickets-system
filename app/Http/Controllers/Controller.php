<?php

namespace App\Http\Controllers;

use App\Models\Event;

abstract class Controller
{
    /**
     * Message for an unexpected error shown to the user: the exception detail only
     * when APP_DEBUG is on (it can contain SQL, file paths or API responses).
     */
    protected function userError(string $prefix, \Throwable $e): string
    {
        return config('app.debug') ? $prefix . ': ' . $e->getMessage() : $prefix . '. Please try again.';
    }

    /**
     * Abort with 403 unless the current user is an admin or owns the event.
     * Accepts an Event or an event id; a missing event is treated as not owned.
     */
    protected function authorizeEventOwner(Event|int|string|null $event): void
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return;
        }

        if (!$event instanceof Event) {
            $event = $event ? Event::find($event) : null;
        }

        if (!$event || (int) $event->user_id !== (int) $user->id) {
            abort(403, 'Unauthorized action.');
        }
    }
}
