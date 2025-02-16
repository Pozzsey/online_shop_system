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

        // if (!Auth::check()) {
        //     return redirect()->route('register')->with('error', 'You need to register first.');
        // }
        // Validate the request
        $validatedData = $request->validate([
            'orderItems' => 'required|array',
            'orderItems.*.product_id' => 'required|integer|exists:products,id',
            'orderItems.*.size' => 'required|string|max:10',
            'orderItems.*.quantity' => 'required|integer|min:1',
            'orderItems.*.price' => 'required|numeric|min:0'
        ]);

        // Calculate the total price
        $totalPrice = 0;
        foreach ($validatedData['orderItems'] as $item) {
            $totalPrice += $item['price'] * $item['quantity'];
        }

        // Create a new order
        $order = new Order();
        $order->user_id = auth()->user()->id; // Assuming the user is authenticated
        $order->total_price = $totalPrice;
        $order->save();

        // Process each order item
        foreach ($validatedData['orderItems'] as $item) {
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $item['product_id'];
            $orderItem->size = $item['size'];
            $orderItem->quantity = $item['quantity'];
            $orderItem->price = $item['price'];
            $orderItem->save();

            // Remove the corresponding item from the cart
            Cart::where('user_id', auth()->user()->id)->delete();
        }

        // Return a success response
        return response()->json(['success' => true, 'message' => 'Order placed successfully and cart items removed.']);
    }
}
