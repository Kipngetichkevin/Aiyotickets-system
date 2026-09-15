<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use App\Models\TicketOrder; // Import your TicketOrder model

class CartService
{
    public function addToCart(int $ticketTypeId): array
    {
        // Get current cart from session or initialize an empty array
        $shoppingCart = session('shoppingCart', []);

        // Check if the ticket type is already in the cart
        if (isset($shoppingCart[$ticketTypeId])) {
            // Increment the quantity if it exists
            $shoppingCart[$ticketTypeId]['amount'] += 1;
        } else {
            // Fetch ticket type details and add to cart
            $ticketType = TicketOrder::findOrFail($ticketTypeId);
            $shoppingCart[$ticketTypeId] = [
                'ticketTypeId' => $ticketTypeId,
                'amount' => 1,
                'price' => $ticketType->price, // Ensure this property exists
                'name' => $ticketType->name, // Ensure this property exists
                // Add any other properties you need
            ];
        }

        // Update the session with the new cart
        session(['shoppingCart' => $shoppingCart]);
        return $shoppingCart;
    }

    public function removeFromCart(int $ticketTypeId): array | null
    {
        $shoppingCart = session('shoppingCart', []);

        if (!isset($shoppingCart[$ticketTypeId])) {
            return null; // Ticket type not found in cart
        }

        // Decrement or remove the ticket type from the cart
        if ($shoppingCart[$ticketTypeId]['amount'] == 1) {
            unset($shoppingCart[$ticketTypeId]);
        } else {
            $shoppingCart[$ticketTypeId]['amount'] -= 1;
        }

        // Update the session
        session(['shoppingCart' => $shoppingCart]);
        return $shoppingCart;
    }

    public function getCart(): array
    {
        return session('shoppingCart', []);
    }

    public function clearCart(): void
    {
        session()->forget('shoppingCart');
    }
}