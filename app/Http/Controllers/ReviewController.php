<?php

namespace App\Http\Controllers;


use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Storage;

class ReviewController extends Controller
{
    public function getAllReviews(Request $request)
    {
        $productId = $request->query('product_id');

        if (!$productId) {
            return $this->Ok([], "No product ID provided.");
        }

        $reviews = Review::with(['products', 'user.profile'])
            ->whereHas('products', function ($query) use ($productId) {
                $query->where('products.id', $productId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return $this->Ok($reviews, "Reviews retrieved successfully.");
    }


    public function getProductRating(Request $request){
        $ratings = DB::table('review_product')
        ->select('product_id', DB::raw('AVG(star_rating) as average_rating'))
        ->groupBy('product_id')
        ->get();

        return response()->json($ratings);
    }

    public function store(Request $request){
        $validator = validator()->make($request->all(), [
            "products" => "required|array",
            "products.*" => "array",
            "products.*.id" => "required|exists:products,id",
            "products.*.review_text" => "required|string|max:2000",
            "products.*.star_rating" => "required|integer|min:1|max:5",
            "images" => "nullable|array",
            "images.*" => "image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096"
        ]);
    
        if ($validator->fails()) {
            return $this->BadRequest($validator);
        }
    
        $review = Review::create([
            'user_id' => $request->user()->id
        ]);
    
        foreach ($request->products as $index => $product) {
            $extension = null;
    
            if ($request->hasFile("images") && isset($request->file("images")[$index])) {
                $image = $request->file("images")[$index];
                $extension = $image->getClientOriginalExtension();
                Storage::disk("public")->putFileAs("/uploads/reviews", $image, "{$review->id}-{$product['id']}.{$extension}");
            }
    
            $review->products()->attach($product['id'], [
                'review_text' => $product['review_text'],
                'star_rating' => $product['star_rating'],
                'extension' => $extension
            ]);
        }
    
        return $this->Created($review->load('products'), "Review has been created!");
    }
    
}
