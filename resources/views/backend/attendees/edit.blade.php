@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-4">Edit Attendee</h1>

    <form action="{{ route('attendees.update', $attendee->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" id="name" name="name" class="mt-1 block w-full" value="{{ $attendee->name }}" required>
        </div>

        <div class="mb-4">
            <label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
            <input type="text" id="gender" name="gender" class="mt-1 block w-full" value="{{ $attendee->gender }}" required>
        </div>

        <div class="mb-4">
            <label for="transaction_code" class="block text-sm font-medium text-gray-700">Transaction Code</label>
            <input type="text" id="transaction_code" name="transaction_code" class="mt-1 block w-full" value="{{ $attendee->transaction_code }}">
        </div>

        <div class="mb-4">
            <label for="mode_of_payment" class="block text-sm font-medium text-gray-700">Mode of Payment</label>
            <select id="mode_of_payment" name="mode_of_payment" class="mt-1 block w-full" required>
                <option value="crypto" {{ $attendee->mode_of_payment == 'crypto' ? 'selected' : '' }}>Crypto</option>
                <option value="mobile_money" {{ $attendee->mode_of_payment == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                <option value="card" {{ $attendee->mode_of_payment == 'card' ? 'selected' : '' }}>Card</option>
            </select>
        </div>

        <div class="mb-4">
            <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
            <input type="text" id="phone" name="phone" class="mt-1 block w-full" value="{{ $attendee->phone }}" required>
        </div>

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" id="email" name="email" class="mt-1 block w-full" value="{{ $attendee->email }}" required>
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update Attendee</button>
    </form>
</div>
@endsection
