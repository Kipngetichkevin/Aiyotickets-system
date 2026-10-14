<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinanceCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Expense categories. Used ones can be renamed or hidden, never deleted. */
class CategoryController extends Controller
{
    public function index()
    {
        return view('finance.categories', [
            'categories' => FinanceCategory::withSum('payments as spent', 'amount')->withCount('bills')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:100|unique:finance_categories,name']);
        FinanceCategory::create($data);

        return back()->with('success', "Category “{$data['name']}” added.");
    }

    public function update(Request $request, FinanceCategory $category)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:100', Rule::unique('finance_categories', 'name')->ignore($category->id)],
            'is_active' => 'nullable|boolean',
        ]);
        $category->update(['name' => $data['name'], 'is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'Category saved.');
    }
}
