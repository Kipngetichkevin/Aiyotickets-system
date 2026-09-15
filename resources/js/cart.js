// Update the cart count when the cart is updated
$.ajax({
    type: 'POST',
    url: '/cart/add/' + eventId,
    success: function(data) {
        // Update the cart count
        $('.cart-count').text(data.count);
    }
});