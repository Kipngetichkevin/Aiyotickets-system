<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrganiserApplicationDecided;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Admin review of organiser applications. Approving grants the merchant role;
 * suspending takes it away (their events stay up, they just can't manage them).
 */
class OrganiserController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', Merchant::PENDING);
        $search = trim((string) $request->input('q'));

        $organisers = Merchant::with(['user', 'reviewer'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('kra_pin', 'like', "%{$search}%")))
            ->orderByRaw("status = 'pending' desc")
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.organisers.index', [
            'organisers' => $organisers,
            'status'     => $status,
            'search'     => $search,
            'counts'     => Merchant::selectRaw('status, count(*) n')->groupBy('status')->pluck('n', 'status'),
        ]);
    }

    public function approve(Merchant $merchant)
    {
        if (!in_array($merchant->status, [Merchant::PENDING, Merchant::REJECTED, Merchant::SUSPENDED], true)) {
            return back()->with('error', "{$merchant->name} is already approved.");
        }

        return $this->decide($merchant, Merchant::APPROVED, null, "{$merchant->name} is now an organiser.");
    }

    public function reject(Request $request, Merchant $merchant)
    {
        $note = $request->validate(['note' => 'required|string|max:500'])['note'];

        if ($merchant->status !== Merchant::PENDING) {
            return back()->with('error', 'Only pending applications can be rejected.');
        }

        return $this->decide($merchant, Merchant::REJECTED, $note, "Application from {$merchant->name} rejected.");
    }

    public function suspend(Request $request, Merchant $merchant)
    {
        $note = $request->validate(['note' => 'required|string|max:500'])['note'];

        if ($merchant->status !== Merchant::APPROVED) {
            return back()->with('error', 'Only approved organisers can be suspended.');
        }

        return $this->decide($merchant, Merchant::SUSPENDED, $note, "{$merchant->name} has been suspended.");
    }

    private function decide(Merchant $merchant, string $status, ?string $note, string $message)
    {
        DB::transaction(function () use ($merchant, $status, $note) {
            $merchant->forceFill([
                'status'      => $status,
                'review_note' => $note,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ])->save();

            $user = $merchant->user;
            if ($user && !$user->hasRole('admin')) {
                if ($status === Merchant::APPROVED) {
                    $user->syncRoles(['merchant']);
                } elseif ($user->hasRole('merchant')) {
                    $user->syncRoles(['user']);
                }
            }
        });

        if ($email = $merchant->user?->email ?? $merchant->email) {
            Mail::to($email)->queue(new OrganiserApplicationDecided($merchant));
        }

        return back()->with('success', $message);
    }
}
