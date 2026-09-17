<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');

        $query = Budget::with('event')->orderByDesc('created_at');

        if (!$isAdmin) {
            $eventIds = Event::where('user_id', $user->id)->pluck('id');
            $query->whereIn('event_id', $eventIds);
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $budgets = $query->paginate(15)->withQueryString();

        $statsQuery = Budget::query();
        if (!$isAdmin) {
            $eventIds = $eventIds ?? Event::where('user_id', $user->id)->pluck('id');
            $statsQuery->whereIn('event_id', $eventIds);
        }

        $stats = [
            'total'       => (clone $statsQuery)->count(),
            'billed'      => (clone $statsQuery)->sum('cost'),
            'paid'        => (clone $statsQuery)->sum('paid_amount'),
            'outstanding' => (clone $statsQuery)->sum('cost') - (clone $statsQuery)->sum('paid_amount'),
        ];

        $events = $isAdmin
            ? Event::orderBy('name')->get(['id', 'name'])
            : Event::where('user_id', $user->id)->orderBy('name')->get(['id', 'name']);

        return view('budgets.index', compact('budgets', 'events', 'isAdmin', 'stats'));
    }

    public function create()
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');

        $events = $isAdmin
            ? Event::orderBy('name')->get(['id', 'name'])
            : Event::where('user_id', $user->id)->orderBy('name')->get(['id', 'name']);

        return view('budgets.create', compact('events'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id'           => 'required|exists:events,id',
            'name'               => 'required|string|max:255',
            'point_of_contact'   => 'nullable|string|max:255',
            'phone'              => 'nullable|string|max:20',
            'email'              => 'nullable|email|max:255',
            'address'            => 'nullable|string|max:255',
            'role'               => 'nullable|string|max:255',
            'category'           => 'required|in:personnel,supplier,debtor,creditor',
            'cost'               => 'nullable|numeric|min:0',
            'paid_amount'        => 'nullable|numeric|min:0',
            'status'             => 'nullable|in:pending,partial,paid,overdue',
            'contract_signature' => 'nullable|string|max:255',
            'profile_pic'        => 'nullable|image|max:2048',
        ]);

        $profilePicPath = null;
        if ($request->hasFile('profile_pic')) {
            $profilePicPath = $request->file('profile_pic')->store('budget_pics', 'public');
        }

        $budget = Budget::create([
            'event_id'           => $validated['event_id'],
            'name'               => $validated['name'],
            'point_of_contact'   => $validated['point_of_contact'] ?? null,
            'phone'              => $validated['phone'] ?? null,
            'email'              => $validated['email'] ?? null,
            'address'            => $validated['address'] ?? null,
            'role'               => $validated['role'] ?? null,
            'category'           => $validated['category'],
            'cost'               => $validated['cost'] ?? 0,
            'paid_amount'        => $validated['paid_amount'] ?? 0,
            'status'             => $validated['status'] ?? 'pending',
            'contract_signature' => $validated['contract_signature'] ?? null,
            'profile_pic'        => $profilePicPath,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Budget item created successfully.',
                'budget'  => $budget,
            ]);
        }

        return redirect()->route('budgets.index')->with('success', 'Budget item created successfully.');
    }

    public function show($id)
    {
        $budget = Budget::with('event')->findOrFail($id);
        $this->authorizeBudget($budget);
        return view('budgets.show', compact('budget'));
    }

    public function edit($id)
    {
        $budget = Budget::findOrFail($id);
        $this->authorizeBudget($budget);

        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');

        $events = $isAdmin
            ? Event::orderBy('name')->get(['id', 'name'])
            : Event::where('user_id', $user->id)->orderBy('name')->get(['id', 'name']);

        return view('budgets.edit', compact('budget', 'events'));
    }

    public function update(Request $request, $id)
    {
        $budget = Budget::findOrFail($id);
        $this->authorizeBudget($budget);

        $validated = $request->validate([
            'event_id'           => 'required|exists:events,id',
            'name'               => 'required|string|max:255',
            'point_of_contact'   => 'nullable|string|max:255',
            'phone'              => 'nullable|string|max:20',
            'email'              => 'nullable|email|max:255',
            'address'            => 'nullable|string|max:255',
            'role'               => 'nullable|string|max:255',
            'category'           => 'required|in:personnel,supplier,debtor,creditor',
            'cost'               => 'nullable|numeric|min:0',
            'paid_amount'        => 'nullable|numeric|min:0',
            'status'             => 'nullable|in:pending,partial,paid,overdue',
            'contract_signature' => 'nullable|string|max:255',
            'profile_pic'        => 'nullable|image|max:2048',
        ]);

        $data = [
            'event_id'           => $validated['event_id'],
            'name'               => $validated['name'],
            'point_of_contact'   => $validated['point_of_contact'] ?? null,
            'phone'              => $validated['phone'] ?? null,
            'email'              => $validated['email'] ?? null,
            'address'            => $validated['address'] ?? null,
            'role'               => $validated['role'] ?? null,
            'category'           => $validated['category'],
            'cost'               => $validated['cost'] ?? 0,
            'paid_amount'        => $validated['paid_amount'] ?? 0,
            'status'             => $validated['status'] ?? 'pending',
            'contract_signature' => $validated['contract_signature'] ?? null,
        ];

        if ($request->hasFile('profile_pic')) {
            if ($budget->profile_pic) {
                Storage::disk('public')->delete($budget->profile_pic);
            }
            $data['profile_pic'] = $request->file('profile_pic')->store('budget_pics', 'public');
        }

        $budget->update($data);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Budget item updated successfully.',
                'budget'  => $budget->fresh(),
            ]);
        }

        return redirect()->route('budgets.index')->with('success', 'Budget item updated successfully.');
    }

    public function destroy($id)
    {
        $budget = Budget::findOrFail($id);
        $this->authorizeBudget($budget);

        if ($budget->profile_pic) {
            Storage::disk('public')->delete($budget->profile_pic);
        }

        $budget->delete();

        return redirect()->route('budgets.index')->with('success', 'Budget item deleted.');
    }

    private function authorizeBudget(Budget $budget): void
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            return;
        }

        $owns = Event::where('id', $budget->event_id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$owns) {
            abort(403, 'Unauthorized action.');
        }
    }
}