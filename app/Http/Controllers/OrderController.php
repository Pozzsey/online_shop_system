<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function Laravel\Prompts\alert;

class OrderController extends Controller
{

    public function store(Request $request)
    {
        // Debugging to check incoming data
        // dd($request->all());

        // Retrieve all cart product IDs from the request
        $cartProductIds = collect($request->orderItems)->pluck('product_id')->toArray();

        // Fetch the actual product IDs from the database (assuming `cart_items` table has `real_product_id`)
        $realProducts = Cart::whereIn('id', $cartProductIds)->pluck('product_id', 'id')->toArray();

        // Transform request data to replace cart IDs with actual product IDs
        $updatedOrderItems = collect($request->orderItems)->map(function ($item) use ($realProducts) {
            if (isset($realProducts[$item['product_id']])) {
                $item['product_id'] = $realProducts[$item['product_id']]; // Replace cart ID with real product ID
            } else {
                abort(400, 'Invalid product in cart.');
            }
            return $item;
        })->toArray();

        // Validate the request using actual product IDs
        $validatedData = validator()->make(
            ['orderItems' => $updatedOrderItems],
            [
                'orderItems' => 'required|array',
                'orderItems.*.product_id' => 'required|integer|exists:products,id',
                'orderItems.*.size' => 'required|string|max:10',
                'orderItems.*.quantity' => 'required|integer|min:1',
                'orderItems.*.price' => 'required|numeric|min:0'
            ]
        )->validate();

        // Calculate total price
        $totalPrice = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $validatedData['orderItems']));

        // Create new order
        $order = new Order();
        $order->user_id = auth()->id();
        $order->total_price = $totalPrice;
        $order->save();

        // Process each order item
        foreach ($validatedData['orderItems'] as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'size' => $item['size'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }

        // Remove cart items after successful order placement
        Cart::whereIn('id', $cartProductIds)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully and cart items removed.',
            'redirect' => route('shop', ['name' => Auth::user()->vendor->shop_name])
        ]);
    }
}
