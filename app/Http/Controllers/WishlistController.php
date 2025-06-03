<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{

    public function index()
    {
        $userId = Auth::id();

        if (!$userId) {
            return $this->Unauthorized("Unauthorized access.");
        }

        $wishlists = Wishlist::with('products')->where('user_id', $userId)->get();

        if ($wishlists->isEmpty()) {
            return $this->Ok([], "No Wishlist found for this user.");
        }

        return $this->Ok($wishlists, "Wishlist retrieved successfully.");
    }


    public function show(Wishlist $wishlist)
    {
        $wishlist->load('products');
        return $this->Ok($wishlist, "Wishlist retrieved with products.");
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

        $product = $wishlist->products()->wherePivot('product_id', $request->product_id)->first();

        if (!$product) {
            return $this->NotFound("Product not found in wishlist!");
        }

        $wishlist->products()->newPivotStatement()
            ->where('product_id', $request->product_id)
            ->delete();

        $wishlist->refresh();

        if ($wishlist->products()->count() === 0) {
            $wishlist->delete();
        }

        return $this->Ok(null, "Product removed successfully from wishlist!");
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

        $user = $request->user();
        $wishlist = $user->wishlists()->firstOrCreate(["user_id" => $user->id]);

        foreach($request->products as $product){
            $existingItems = $wishlist->products()->pluck('id')->toArray();

            if (in_array($product["id"], $existingItems)) {
                return response()->json(["message" => "It's already in the wishlist."], 200);
            }

            $wishlist->products()->syncWithoutDetaching([
                $product["id"] => [
                    "quantity" => $product["quantity"],
                ],
            ]);
        }

        return $this->Created($wishlist, "Product added to wishlist successfully!");
    }


}