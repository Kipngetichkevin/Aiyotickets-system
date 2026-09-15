<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @vite('resources/css/app.css')
</head>
<body>
    
<!-- Display the cart count in the UI -->
<span class="cart-count">{{ session()->get('cart.count', 0) }}</span>

<!-- cart/index.blade.php -->

<h1>Cart Contents</h1>

<ul>
    @foreach ($cart as $eventId => $event)
        <li>
            {{ $event['name'] }} ({{ $event['ticket_id'][$ticketTypeId]['quantity'] }})
        </li>
    @endforeach
</ul>