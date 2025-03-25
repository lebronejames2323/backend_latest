<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{

    public function index()
    {
        try {
            $userId = Auth::id();

            if (!$userId) {
                return $this->Unauthorized("Unauthorized access.");
            }

            $carts = Cart::with('products')->where('user_id', $userId)->get();

            if ($carts->isEmpty()) {
                return $this->Ok([], "No carts found for this user.");
            }

            return $this->Ok($carts, "Carts retrieved successfully.");
        } catch (\Exception $e) {
            \Log::error('Cart fetching error: ' . $e->getMessage());
            return $this->BadRequest(null, "Something went wrong.");
        }
    }


    public function show(Cart $cart)
    {
        $cart->load('products');
        return $this->Ok($cart, "Cart retrieved with products.");
    }

    public function destroy(Cart $cart){

        $cart->delete();

        return $this->Ok(null, "Deleted!");
    }

    public function deleteProduct(Request $request, $cartId)
{
    $validator = validator()->make($request->all(), [
        'product_id' => 'required|exists:products,id',
    ]);

    if ($validator->fails()) {
        return $this->BadRequest($validator);
    }

    $cart = Cart::find($cartId);

    if (!$cart) {
        return $this->NotFound("Cart not found!");
    }

    $product = $cart->products()->where('product_id', $request->product_id)->first();

    if (!$product) {
        return $this->NotFound("Product not found in cart!");
    }

    $cart->delete();

    return $this->Ok(null, "Product deleted successfully from the cart!");
}


    public function updateProductQuantity(Request $request, $cartId)
{
    $validator = validator()->make($request->all(), [
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1|max:1000000',
    ]);

    if ($validator->fails()) {
        return $this->BadRequest($validator);
    }

    $cart = Cart::find($cartId);

    if (!$cart) {
        return $this->NotFound("Cart not found!");
    }

    $product = $cart->products()->where('product_id', $request->product_id)->first();

    if (!$product) {
        return $this->NotFound("Product not found in cart!");
    }

    $cart->products()->updateExistingPivot($request->product_id, [
        'quantity' => $request->quantity,
    ]);

    return $this->Ok(null, "Product quantity updated successfully!");
}


    public function store(Request $request){
        $validator = validator()->make($request->all(), [
            "products" => "required|array",
            "products.*" => "array",
            "products.*.id" => "required|exists:products,id",
            "products.*.quantity" => "required|min:1|max:1000000|int"
        ]);

        if ($validator->fails()){
            return $this->BadRequest($validator);
        }
        
        $cart = $request->user()->carts()->create($validator->validated());

        $items = [];

        foreach($request->products as $product){
            $items[$product["id"]] = [
            "quantity" => $product["quantity"],
            ];
        }

        $cart->products()->sync($items);

        $cart->products;

        return $this->Created($cart, "Cart has been added!");
    }
}