<?php

namespace App\Http\Controllers;

use App\Models\GatePass;
use App\Services\CheckIn;
use Illuminate\Http\Request;

/**
 * Door staff with a gate link: no account, scan-only, one event or experience.
 * A PIN, when set, is asked once per browser session — valid for UNLOCK_TTL_MINUTES.
 */
class GateController extends Controller
{
    /** How long an unlocked gate stays unlocked in the browser session. */
    private const UNLOCK_TTL_MINUTES = 480; // 8 hours

    public function __construct(private CheckIn $checkIn)
    {
    }

    public function show(Request $request, string $token)
    {
        $pass = $this->pass($token, requireUnlocked: false);

        if (!$pass) {
            return response()->view('qrcode.gate-closed', [], 410);
        }
        if (!$this->unlocked($request, $pass)) {
            return view('qrcode.gate-pin', ['pass' => $pass]);
        }

        $pass->forceFill(['last_used_at' => now()])->save();
        $target = $pass->event ?? $pass->experience;

        return view('qrcode.gate', [
            'title'     => $target->name,
            'subtitle'  => $pass->label,
            'verifyUrl' => route('gate.scan', $token),
            'searchUrl' => route('gate.search', $token),
            'undoUrl'   => null,
            'payload'   => [],
            'stats'     => $pass->event
                ? $this->checkIn->eventStats($pass->event)
                : $this->checkIn->experienceStats($pass->experience),
        ]);
    }

    public function unlock(Request $request, string $token)
    {
        $pass = $this->pass($token, requireUnlocked: false);
        abort_unless($pass, 410);

        if (!$pass->checkPin((string) $request->input('pin'))) {
            return back()->withErrors(['pin' => 'Wrong PIN. Ask the organiser for the gate PIN.']);
        }

        $request->session()->put("gate_unlocked.{$pass->id}", [
            'at' => now()->timestamp,
        ]);

        return redirect()->route('gate.show', $token);
    }

    public function scan(Request $request, string $token)
    {
        $request->validate(['qr' => 'required|string|max:2000']);

        if (!$pass = $this->pass($token, $request)) {
            return response()->json([
                'success' => false,
                'message' => 'This gate link is closed. Ask the organiser for a new one.',
            ], 410);
        }

        [$status, $body] = $pass->event
            ? $this->checkIn->scanEvent($pass->event, $request->input('qr'), ['pass' => $pass->id])
            : $this->checkIn->scanExperience($pass->experience, $request->input('qr'), ['pass' => $pass->id]);

        $pass->forceFill([
            'last_used_at' => now(),
            'scans'        => $pass->scans + 1,
        ])->save();

        return response()->json($body, $status);
    }

    public function search(Request $request, string $token)
    {
        abort_unless($pass = $this->pass($token, $request), 410);

        $q = CheckIn::term($request->input('q'));

        return response()->json([
            'results' => $pass->event
                ? $this->checkIn->searchEvent($pass->event, $q)
                : $this->checkIn->searchExperience($pass->experience, $q),
        ]);
    }

    private function pass(string $token, ?Request $request = null, bool $requireUnlocked = true): ?GatePass
    {
        $pass = GatePass::with(['event', 'experience'])->where('token', $token)->first();

        if (!$pass
            || !$pass->isActive()
            || (!$pass->event && !$pass->experience)
            || $pass->event?->status === 'cancelled') {
            return null;
        }
        if ($requireUnlocked && !$this->unlocked($request ?? request(), $pass)) {
            return null;
        }

        return $pass;
    }

    private function unlocked(Request $request, GatePass $pass): bool
    {
        if (!$pass->pin_hash) {
            return true; // no PIN set — always open
        }

        $state = $request->session()->get("gate_unlocked.{$pass->id}");
        if (!is_array($state) || empty($state['at'])) {
            return false;
        }

        $expiresAt = $state['at'] + (self::UNLOCK_TTL_MINUTES * 60);
        if (now()->timestamp > $expiresAt) {
            $request->session()->forget("gate_unlocked.{$pass->id}");
            return false;
        }

        return true;
    }
}