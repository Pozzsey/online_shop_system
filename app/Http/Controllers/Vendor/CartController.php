<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cartItems = Cart::where('user_id', auth()->id())
        ->with('product')
        ->get();

        $subTotal = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        // dd($cartItems);
        return view('user.cart.index', compact( 'cartItems','subTotal'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // Check if the user is logged in
    if (!auth()->check()) {
        return redirect()->route('register')->with('error', 'You need to register or log in to add items to the cart.');
    }

    // Validate the incoming request
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
    ]);

    // Add or update the cart item
    Cart::updateOrCreate(
        [
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
        ],
        [
            'quantity' => DB::raw("quantity + {$request->quantity}"),
        ]
    );

    return redirect()->back()->with('success', 'Item added to cart!');
}

public function addToCart(Request $request)
{
    // dd($request->all());
    $validated = $request->validate([
        'product_id' => 'required|exists:products,id',
        'size' => 'required',
        'quantity' => 'required|integer|min:1',
    ]);

    $cart = Cart::firstOrCreate([
        'user_id' => auth()->id(),
        'product_id' => $validated['product_id'],
        'size' => $validated['size'],
        'quantity' => $validated['quantity'],
    ]);


    return response()->json([
        'message' => 'Product added to cart', 'cart' => $cart,
        'redirect' => route('cart.index', ['name' => Auth::user()->vendor->shop_name])
    ]);
}



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        return view('user.cart.index', compact('cartItems', 'products'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $name, string $id)
{
    // Log the request data for debugging
    \Log::info('Update Request: ', ['name' => $name, 'id' => $id, 'quantity' => $request->quantity]);

    // Retrieve the cart item by ID, or fail if not found
    $cartItem = Cart::with('user.vendor')->findOrFail($id);

    // Check if the cart item has a related user and vendor
    if (!$cartItem->user || !$cartItem->user->vendor) {
        return response()->json(['success' => false, 'message' => 'Vendor not found for this cart item.'], 400);
    }

    // Validate that the vendor's shop_name matches the provided name
    if ($cartItem->user->vendor->shop_name !== $name) {
        return response()->json(['success' => false, 'message' => 'Vendor shop name does not match.'], 400);
    }

    // Update the quantity
    $cartItem->quantity = $request->quantity;
    $cartItem->save();

    // Return a success response
    return response()->json(['success' => true, 'message' => 'Cart updated successfully.']);
}



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $cartItem = Cart::where('id', $id)->where('user_id', auth()->id())->first();

        if ($cartItem) {
            $cartItem->delete();
            return redirect()->back()->with('success', 'Item removed from cart.');
        }

        return redirect()->back()->with('error', 'Item not found in your cart.');
    }
}
