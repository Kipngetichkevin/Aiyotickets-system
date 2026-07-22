<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @vite('resources/css/app.css')
  <!-- Include Preline CSS -->
  <link href="https://unpkg.com/preline/dist/preline.css" rel="stylesheet"/>
</head>
<body>
@include('includes.header')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Your Cart</h1>
    
    @if(session('error'))
        <div class="bg-red-500 text-white p-4 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    @if(!empty($cart))
        <div class="overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Event</th>
                        <th scope="col" class="px-6 py-3">Ticket Type</th>
                        <th scope="col" class="px-6 py-3">Quantity</th>
                        <th scope="col" class="px-6 py-3">Price</th>
                        <th scope="col" class="px-6 py-3">Total</th>
                        <th scope="col" class="px-6 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalAmount = 0; @endphp
                    @foreach($cart as $ticket_id => $item)
                        <tr class="border-b dark:border-gray-700">
                            <td class="px-6 py-4">{{ $item['event_name'] ?? 'Event' }}</td>
                            <td class="px-6 py-4">{{ $item['ticket_name'] ?? 'Ticket' }}</td>
                            <td class="px-6 py-4">{{ $item['quantity'] ?? 0 }}</td>
                            <td class="px-6 py-4">KES {{ number_format($item['price'] ?? 0, 2) }}</td>
                            <td class="px-6 py-4">KES {{ number_format(($item['quantity'] ?? 0) * ($item['price'] ?? 0), 2) }}</td>
                            <td class="px-6 py-4">
                                <form action="{{ route('cart.remove', ['ticket_id' => $ticket_id]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-red-500 hover:text-red-700">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @php 
                            $totalAmount += ($item['quantity'] ?? 0) * ($item['price'] ?? 0);
                        @endphp
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <p class="font-semibold text-xl">Total Amount: KES {{ number_format($totalAmount, 2) }}</p>
        </div>

        <div class="mt-6">
            <a href="{{ route('cart.view') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out">
                Proceed to Checkout
            </a>
        </div>
    @else
        <p class="text-xl font-medium text-gray-600">Your cart is empty.</p>
        <a href="{{ route('home') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-800">
            Continue Shopping
        </a>
    @endif
</div>
@include('includes.footer')
</body>
</html>