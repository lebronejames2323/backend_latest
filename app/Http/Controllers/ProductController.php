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
            "description" => "required|max:255|string",
            "price" => "required|numeric|max:999999999|min:0",
            "image" => "required|image",
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
        
        Storage::disk("public")->putFileAs("/uploads", $request->file("image"), "$product->id.$extension");

        return $this->Created($product);
    }
    public function update(Request $request, Product $product){
        $validator = validator()->make($request->all(), [
            "name" => "sometimes|max:255|unique:products,name,$product->id|string",
            "description" => "sometimes|max:255|string",
            "price" => "sometimes|numeric|max:999999999|min:0",
            "image" => "sometimes|image",
            "category_id" => "sometimes|exists:categories,id",
            "stock" => "sometimes|max:2000000000|min:1|int",
        ]);

        if ($validator->fails()){
            return $this->BadRequest($validator);
        }

        $validated = $validator->validated();
        $extension = $request->file("image")->getClientOriginalExtension();
        $validated['extension'] = $extension;
        Storage::disk("public")->putFileAs("/uploads", $request->file("image"), "$product->id.$extension");

        $product->update($validated);

        return $this->Ok($product, "Updated!");
    }
    public function destroy(Product $product){

        $product->delete();

        return $this->Ok(null, "Deleted!");
    }

    public function show(Product $product){
        $product->category;

        return $this->Ok($product,"Retrieved!");
    }
}
