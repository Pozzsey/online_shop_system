@extends('user.layout.main')

@section('content')

<section id="page-header" class="about-header" style="height: 250px">
    <h2>#cart</h2>
    <p>Add your coupon code & SAVE up to 70%!</p>
</section>

<section id="cart" class="section-p1">
    <table width="100%">
        <thead style="padding: 10px">
            <tr>
                <td>Remove</td>
                <th>Image</th>
                <th>Product</th>
                <th>Size</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($cartItems as $item)
            <tr>
                <td>
                    <a href="{{ route('cart.destroy', $item->id) }}"
                        onclick="event.preventDefault(); document.getElementById('delete-form-{{ $item->id }}').submit();">
                        <i class="far fa-times-circle"></i>
                    </a>
                    <form id="delete-form-{{ $item->id }}" action="{{ route('cart.destroy', $item->id) }}" method="POST"
                        style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </td>
                <td>
                    <img src="{{ asset('storage/' . $item->product->image_url) }}"
                         alt="{{ $item->product->name }}"
                         style="width: 50px">
                </td>
                <td>{{ $item->product->name }}</td>
                <td class="size">{{ $item->size }}</td>
                <td class="price">{{ number_format($item->product->price, 2) }}</td>
                <td>
                    <input type="number" min="1" class="quantity" data-id="{{ $item->id }}"
                           value="{{ $item->quantity }}"
                           style="text-align: center; width: 50px;">
                </td>
                <td class="subtotal" style="text-align: center">
                    {{ number_format($item->product->price * $item->quantity, 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</section>

<section id="cart-add" class="section-p1">
    <div id="coupon">
        <h3>Apply Coupon</h3>
        <div>
            <input type="text" placeholder="Enter Your Coupon">
            <button class="normal">Apply</button>
        </div>
    </div>

    <div id="subtotal">
        <h3>Cart Totals</h3>
        <table>
            <tbody>
                <tr>
                    <td>Cart Subtotal</td>
                    <td id="cart-subtotal">$0.00</td>
                </tr>
                <tr>
                    <td>Shipping</td>
                    <td>Free</td>
                </tr>
                <tr>
                    <td><strong>Total</strong></td>
                    <td id="cart-total"><strong>$0.00</strong></td>
                </tr>
            </tbody>
        </table>
        <button class="normal" id="place-order">Proceed to checkout</button>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {

    function updateTotals() {
        let cartSubtotal = 0;

        $('.quantity').each(function() {
            let $row = $(this).closest('tr');
            let price = parseFloat($row.find('.price').text().replace(/,/g, ''));
            let quantity = parseInt($(this).val());
            let subtotal = price * quantity;

            $row.find('.subtotal').text(subtotal.toFixed(2));

            cartSubtotal += subtotal;
        });

        $('#cart-subtotal').text('$' + cartSubtotal.toFixed(2));
        $('#cart-total').html('<strong>$' + cartSubtotal.toFixed(2) + '</strong>');
    }

    updateTotals();

    $('.quantity').on('input', function() {
        let $row = $(this).closest('tr');
        let quantity = $(this).val();
        let itemId = $(this).data('id');
        let shopName = '{{ Auth::user()->vendor->shop_name ?? '' }}';

        updateTotals();

        $.ajax({
            url: `/cart/update/${shopName}/${itemId}`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            data: { quantity: quantity },
            success: function(response) {
                console.log(response);
            },
            error: function(xhr) {
                console.error(xhr.responseText);
            }
        });
    });

    $('#place-order').on('click', function() {
        let orderData = [];

        $('.quantity').each(function() {
            let $row = $(this).closest('tr');
            let productId = $(this).data('id');
            let size = $row.find('.size').text().trim();
            let quantity = $(this).val();
            let price = parseFloat($row.find('.price').text().replace(/,/g, ''));

            orderData.push({ product_id: productId, size: size, quantity: quantity, price: price });
        });

        $.ajax({
    url: '/order',
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    data: { orderItems: orderData },
    success: function(response) {
        // Show SweetAlert message
        Swal.fire({
            title: 'Order Placed!',
            text: 'Your order has been successfully placed.',
            icon: 'success',
            timer: 1000, // The alert will close after 1 second (adjust as needed)
            timerProgressBar: true,
            didClose: () => {
                // Redirect to the shop page after the alert closes
                window.location.href = response.redirect; // Redirect based on the server's response
            }
        });
    },
    error: function(xhr) {
        console.error(xhr.responseText);
        Swal.fire({
            title: 'Error!',
            text: 'Failed to place order. Please add item to cart first.',
            icon: 'error'
        });
    }
});

    });

});
</script>

@endsection
