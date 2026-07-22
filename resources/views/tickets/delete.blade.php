@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-4 sm:space-y-6">

    <!-- Content -->
    <div class="w-full lg:ps-64">
<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-4">{{ $event->title }} - Tickets</h1>

    <a href="{{ route('tickets.create', $event->id) }}" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">Add New Ticket</a>

    @if (session('success'))
        <div class="bg-green-500 text-white p-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <table class="min-w-full bg-white rounded-lg shadow-lg">
        <thead>
            <tr>
                <th class="py-2 px-4 border-b">Ticket Name</th>
                <th class="py-2 px-4 border-b">Price</th>
                <th class="py-2 px-4 border-b">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tickets as $ticket)
            <tr>
                <td class="py-2 px-4 border-b">{{ $ticket->name }}</td>
                <td class="py-2 px-4 border-b">${{ number_format($ticket->price, 2) }}</td>
                <td class="py-2 px-4 border-b">
                    <a href="{{ route('tickets.edit', $ticket->id) }}" class="text-blue-500">Edit</a>
                    <form action="{{ route('tickets.destroy', $ticket->id) }}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 ml-2">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
</div>
</div>
@endsection