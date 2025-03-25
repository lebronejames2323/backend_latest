<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{

    public function index()
    {
        try {
            $userId = Auth::id();

            if (!$userId) {
                return $this->Unauthorized("Unauthorized access.");
            }

            $wishlists = Wishlist::with('products')->where('user_id', $userId)->get();

            if ($wishlists->isEmpty()) {
                return $this->Ok([], "No Wishlist found for this user.");
            }

            return $this->Ok($wishlists, "Wishlist retrieved successfully.");
        } catch (\Exception $e) {
            \Log::error('Wishlist fetching error: ' . $e->getMessage());
            return $this->BadRequest(null, "Something went wrong.");
        }
    }


    public function show(Wishlist $wishlist)
    {
        $wishlist->load('products');
        return $this->Ok($wishlist, "Wishlist retrieved with products.");
    }

    public function destroy(Wishlist $wishlist){

        $wishlist->delete();

        return $this->Ok(null, "Deleted!");
    }

    public function deleteProduct(Request $request, $wishlistId)
{
    $validator = validator()->make($request->all(), [
        'product_id' => 'required|exists:products,id',
    ]);

    if ($validator->fails()) {
        return $this->BadRequest($validator);
    }

    $wishlist = Wishlist::find($wishlistId);

    if (!$wishlist) {
        return $this->NotFound("Wishlist not found!");
    }

    $product = $wishlist->products()->where('product_id', $request->product_id)->first();

    if (!$product) {
        return $this->NotFound("Product not found in cart!");
    }

    $wishlist->delete();

    return $this->Ok(null, "Product deleted successfully from the cart!");
}


    public function updateProductQuantity(Request $request, $wishlistId)
{
    $validator = validator()->make($request->all(), [
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1|max:1000000',
    ]);

    if ($validator->fails()) {
        return $this->BadRequest($validator);
    }

    $wishlist = Wishlist::find($wishlistId);

    if (!$wishlist) {
        return $this->NotFound("Wishlist not found!");
    }

    $product = $wishlist->products()->where('product_id', $request->product_id)->first();

    if (!$product) {
        return $this->NotFound("Product not found in cart!");
    }

    $wishlist->products()->updateExistingPivot($request->product_id, [
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
        
        $wishlist = $request->user()->wishlists()->create($validator->validated());

        $items = [];

        foreach($request->products as $product){
            $items[$product["id"]] = [
            "quantity" => $product["quantity"],
            ];
        }

        $wishlist->products()->sync($items);

        $wishlist->products;

        return $this->Created($wishlist, "Wishlist has been added!");
    }
}