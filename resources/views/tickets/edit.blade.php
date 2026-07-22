@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-4 sm:space-y-6">

    <!-- Content -->
    <div class="w-full lg:ps-64">
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-4">Edit Ticket: {{ $ticket->name }}</h1>

    <form action="{{ route('tickets.update', $ticket->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="name" class="block text-gray-700">Ticket Name</label>
            <input type="text" name="name" id="name" class="w-full border rounded px-3 py-2" value="{{ $ticket->name }}">
            @error('name')
                <div class="text-red-500">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="price" class="block text-gray-700">Price</label>
            <input type="number" name="price" id="price" class="w-full border rounded px-3 py-2" value="{{ $ticket->price }}">
            @error('price')
                <div class="text-red-500">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update Ticket</button>
    </form>
    </div>
</div>
</div>

@endsection