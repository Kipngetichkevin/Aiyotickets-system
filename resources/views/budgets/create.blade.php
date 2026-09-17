@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <div class="mb-6">
        <a href="{{ route('budgets.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400">&larr; Back to Budgets</a>
        <h1 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">Add Budget Item</h1>
    </div>

    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('budgets.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03] space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Event <span class="text-red-500">*</span></label>
                    <select name="event_id" required class="w-full rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                        <option value="">— Select event —</option>
                        @foreach($events as $ev)
                            <option value="{{ $ev->id }}" @selected(old('event_id') == $ev->id)>{{ $ev->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full rounded-lg border-gray-300 dark:bg-gray-800 text-sm"
                           placeholder="e.g. Sound System Rental">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category <span class="text-red-500">*</span></label>
                    <select name="category" required class="w-full rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                        <option value="supplier" @selected(old('category') === 'supplier')>Supplier</option>
                        <option value="personnel" @selected(old('category') === 'personnel')>Personnel</option>
                        <option value="debtor" @selected(old('category') === 'debtor')>Debtor</option>
                        <option value="creditor" @selected(old('category') === 'creditor')>Creditor</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                    <input type="text" name="role" value="{{ old('role') }}"
                           class="w-full rounded-lg border-gray-300 dark:bg-gray-800 text-sm"
                           placeholder="e.g. Vendor">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Point of Contact</label>
                    <input type="text" name="point_of_contact" value="{{ old('point_of_contact') }}"
                           class="w-full rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                    <input type="text" name="address" value="{{ old('address') }}"
                           class="w-full rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cost (KES)</label>
                    <input type="number" step="0.01" name="cost" value="{{ old('cost', 0) }}"
                           class="w-full rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Paid (KES)</label>
                    <input type="number" step="0.01" name="paid_amount" value="{{ old('paid_amount', 0) }}"
                           class="w-full rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select name="status" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                        <option value="pending" @selected(old('status') === 'pending')>Pending</option>
                        <option value="partial" @selected(old('status') === 'partial')>Partial</option>
                        <option value="paid" @selected(old('status') === 'paid')>Paid</option>
                        <option value="overdue" @selected(old('status') === 'overdue')>Overdue</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contract Signature</label>
                    <input type="text" name="contract_signature" value="{{ old('contract_signature') }}"
                           class="w-full rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Profile Picture</label>
                    <input type="file" name="profile_pic" accept="image/*"
                           class="w-full rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                    <p class="text-xs text-gray-500 mt-1">PNG, JPG up to 2MB</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('budgets.index') }}" class="rounded-lg border px-6 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-800">Cancel</a>
            <button type="submit" class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-blue-700">Create</button>
        </div>
    </form>
</div>
@endsection