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
                <td><img src="{{ asset('storage/' . $item->product->image_url) }}" alt="{{ $item->product->name }}"
                        style="width: 50px"></td>
                <td>{{ $item->product->name }}</td>
                <td class="size">{{ $item->size }}</td>
                <td class="price">{{ $item->product->price }}</td>
                <td><input type="number" min="1" style="text-align: center; width: 50px;" value="{{ $item->quantity }}" class="quantity" data-id="{{ $item->id }}"></td>
                <td class="subtotal" style="text-align: center">{{ $item->product->price * $item->quantity }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</section>

<section id="cart-add" class="section-p1">
    <div id="coupon">
        <h3>Apply Coupon</h3>
        <div>
            <input type="text" name="" id="" placeholder="Enter Your Coupon">
            <button class="normal">Apply</button>
        </div>
    </div>

    <div id="subtotal">
        <h3>Cart Totals</h3>
        <table>
            <tbody>
                <tr>
                    <td>Cart Subtotal</td>
                    <td id="cart-subtotal"></td>
                </tr>
                <tr>
                    <td>Shipping</td>
                    <td>Free</td>
                </tr>
                <tr>
                    <td><strong>Total</strong></td>
                    <td id="cart-total"><strong>$ </strong></td>
                </tr>
            </tbody>
        </table>
        <button class="normal" id="place-order">Proceed to checkout</button>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {

    $('.quantity').on('input', function() {
        var $row = $(this).closest('tr');
        var price = parseFloat($row.find('.price').text());
        var quantity = $(this).val();
        var subtotal = price * quantity;
        $row.find('.subtotal').text(subtotal.toFixed(2));

        updateTotals();

        // AJAX request to update cart item quantity
           // Get the data-id attribute
           var itemId = $(this).data('id');
        var shopName = '{{ Auth::user()->vendor->shop_name }}';

        // AJAX request to update cart item quantity
        $.ajax({
            url: `/cart/update/${shopName}/${itemId}`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                quantity: quantity
            },
            success: function(response) {
                console.log(response);
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
            }
        });
    });

    function updateTotals() {
        var cartSubtotal = 0;
        var cartTotal = 0;

        $('.quantity').each(function() {
            var $row = $(this).closest('tr');
            var price = parseFloat($row.find('.price').text());
            var quantity = $(this).val();
            var subtotal = price * quantity;

            cartSubtotal += subtotal;
            cartTotal += price * quantity;
        });

        $('#cart-subtotal').text(cartSubtotal.toFixed(2));
        $('#cart-total').text('$ ' + cartTotal.toFixed(2));
    }
    updateTotals();

    $('#place-order').on('click', function() {
        var orderData = [];
        console.log(orderData);

        $('.quantity').each(function() {
            var $row = $(this).closest('tr');
            var productId = $row.find('.quantity').data('id');
            var size = $row.find('.size').text();
            console.log(size);

            var quantity = $(this).val();
            var price = parseFloat($row.find('.price').text());

            orderData.push({
                product_id: productId,
                size: size,
                quantity: quantity,
                price: price
            });
        });

        $.ajax({
            url: '/order',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                orderItems: orderData
            },
            success: function(response) {
                console.log(response);
                alert("Order placed successfully!");
                window.location.href = '/order/success';
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
                alert("Failed to place order. Please try again.");
            }
        });
    });
});
</script>

@endsection
