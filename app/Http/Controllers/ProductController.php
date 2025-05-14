<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Storage;

class ProductController extends Controller
{
    public function index(){
        $products = Product::with("category")->get();
        return $this->Ok($products);
    }

    
    public function show($productId){
        $product = Product::with("category")->find($productId);

        if (!$product) {
            return response()->json(["message" => "Product not found"], 404);
        }

        return $this->Ok($product);
    }

    public function featured(){
        $products = Product::with("category")
        ->where("stock", ">", 0)
        ->orderBy("purchase_count", "desc")
        ->take(8)
        ->get();
        return $this->Ok($products);
    }

    public function recommended($excludeProductId = null) {
        $products = Product::with("category")->where("id", "!=", $excludeProductId)->inRandomOrder()->limit(4)->get();

        if ($products->isEmpty()) {
            return response()->json(["message" => "No recommended products found"], 404);
        }

        return $this->Ok($products);
    }

    public function store(Request $request){
        $validator = validator()->make($request->all(), [
            "name" => "required|max:255|unique:products|string",
            "description" => "required|max:2000|string",
            "price" => "required|numeric|max:999999999|min:0",
            "image" => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            "category_id" => "required|exists:categories,id",
            "stock" => "required|max:2000000000|min:1|int",
        ]);

        if ($validator->fails()){
            return $this->BadRequest($validator);
        }
        $validated = $validator->validated();
        $extension = $request->file("image")->getClientOriginalExtension();
        $validated['extension'] = $extension;
        $validated['purchase_count'] = 0;
        
        $product = Product::create($validated);
        
        Storage::disk("public")->putFileAs("/uploads/products", $request->file("image"), "$product->id.$extension");

        return $this->Created($product);
    }
    
    public function update(Request $request, Product $product)
    {
        $validator = validator()->make($request->all(), [
            "name" => "sometimes|max:255|unique:products,name,$product->id|string",
            "description" => "sometimes|max:255|string",
            "price" => "sometimes|numeric|max:999999999|min:0",
            "image" => "sometimes|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096",
            "category_id" => "sometimes|exists:categories,id",
            "stock" => "sometimes|max:2000000000|min:0|int",
        ]);

        if ($validator->fails()) {
            return $this->BadRequest($validator);
        }

        $validated = $validator->validated();

        if ($request->hasFile("image")) {
            $oldImagePath = "/uploads/products/{$product->id}.{$product->extension}";
            if (Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }

            $extension = $request->file("image")->getClientOriginalExtension();
            $validated['extension'] = $extension;
            Storage::disk("public")->putFileAs("/uploads/products", $request->file("image"), "{$product->id}.$extension");
        }

        $product->update($validated);

        return $this->Ok($product, "Updated!");
    }

    
    
    public function destroy(Request $request, $productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return $this->NotFound("Product not found!");
        }

        $product->delete();

        return $this->Ok(null, "Product deleted successfully!");
    }
}
