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
                    </tr>
                </thead>
                <tbody>
                    @foreach($cart as $eventId => $eventData)
                        @if(isset($eventData['ticket_types']))
                            @foreach($eventData['ticket_types'] as $ticketTypeId => $ticketTypeData)
                                <tr class="border-b dark:border-gray-700">
                                    <td class="px-6 py-4">{{ $eventData['name'] }}</td>
                                    <td class="px-6 py-4">{{ $ticketTypeData['name'] }}</td>
                                    <td class="px-6 py-4">{{ $ticketTypeData['quantity'] }}</td>
                                    <td class="px-6 py-4">KES {{ number_format($ticketTypeData['price'], 2) }}</td>
                                    <td class="px-6 py-4">KES {{ number_format($ticketTypeData['price'] * $ticketTypeData['quantity'], 2) }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center">No tickets found for this event.</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <p class="font-semibold text-xl">Total Amount: KES {{ number_format($totalAmount, 2) }}</p>
        </div>

        <div class="mt-6">
            <a href="{{ route('checkout') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out">Proceed to Checkout</a>
        </div>
    @else
        <p class="text-xl font-medium text-gray-600">Your cart is empty.</p>
    @endif
</div>
@include('includes.footer')
</body>
</html>