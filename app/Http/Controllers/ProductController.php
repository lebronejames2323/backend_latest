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


    public function show(Product $product){
        $product->category;

        return $this->Ok($product,"Retrieved!");
    }
}
