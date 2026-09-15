<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Budget;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BudgetController extends Controller
{
    
    public function index()
    {
        $budgets = Budget::all();
        $events = Event::where('user_id', Auth::id())->get(); // Add this line
        return view('backend.budgets.index', compact('budgets', 'events')); // Pass events
    }


    public function create()
    {
        $events = Event::where('user_id', Auth::id())->get();
        return view('backend.budgets.create', compact('events'));
    }
    


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'point_of_contact' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
            'category' => 'required|in:personnel,supplier,debtor,creditor',
            'cost' => 'nullable|numeric',
            'contract_signature' => 'nullable|string|max:255',
            'profile_pic' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $logoPath = null;
        if ($request->hasFile('profile_pic')) {
            $logoPath = $request->file('profile_pic')->store('location', 'public');
        }

        Budget::create([
            'event_id' => $request->input('event_id'),
            'name' => $request->input('name'),
            'point_of_contact' => $request->input('point_of_contact'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'address' => $request->input('address'),
            'role' => $request->input('role'),
            'category' => $request->input('category'),
            'cost' => $request->input('cost'),
            'contract_signature' => $request->input('contract_signature'),
            'profile_pic' => $logoPath,
        ]);

        return redirect()->route('backend.budgets.index')->with('success', 'Budget item created successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'point_of_contact' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
            'category' => 'required|in:personnel,supplier,debtor,creditor',
            'cost' => 'nullable|numeric',
            'contract_signature' => 'nullable|string|max:255',
            'profile_pic' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $budget = Budget::findOrFail($id);

        $logoPath = $budget->profile_pic;
        if ($request->hasFile('profile_pic')) {
            if ($logoPath) {
                \Storage::delete('public/' . $logoPath);
            }
            $logoPath = $request->file('profile_pic')->store('location', 'public');
        }

        $budget->update([
            'name' => $request->input('name'),
            'point_of_contact' => $request->input('point_of_contact'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'address' => $request->input('address'),
            'role' => $request->input('role'),
            'category' => $request->input('category'),
            'cost' => $request->input('cost'),
            'contract_signature' => $request->input('contract_signature'),
            'profile_pic' => $logoPath,
        ]);

        return redirect()->route('backend.budgets.index')->with('success', 'Budget item updated successfully.');
    }

    public function edit($id)
    {
        $budget = Budget::findOrFail($id);
        return view('backend.budgets.edit', compact('budget'));
    }

    public function show($id)
    {
        $budget = Budget::with('services')->findOrFail($id);
        return view('backend.budgets.show', compact('budget'));
    }
}