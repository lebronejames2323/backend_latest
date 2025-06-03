<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Storage;
use Illuminate\Support\Facades\Log;

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

    public function getSalesData() {
        $sales = Product::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(price * purchase_count) as total_revenue")
            ->groupBy("month")
            ->orderBy("month", "ASC")
            ->get();

        $totalIncome = Product::selectRaw("SUM(price * purchase_count) as total_income")->first();

        Log::info('Monthly & Total Income Data:', ['monthly_sales' => $sales, 'total_income' => $totalIncome->total_income]);

        return response()->json([ 'monthly_sales' => $sales, 'total_income' => $totalIncome->total_income ]);
    }

    public function store(Request $request){
        $validator = validator()->make($request->all(), [
            "name" => "required|max:255|unique:products|string",
            "description" => "required|max:2000|string",
            "price" => "required|numeric|max:999999999|min:0",
            "image" => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            "additional_images" => "nullable|array|max:4",
            "additional_images.*" => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            "category_id" => "required|exists:categories,id",
            "stock" => "required|max:2000000000|min:1|int",
        ]);

        if ($validator->fails()){
            return $this->BadRequest($validator);
        }

        $validated = $validator->validated();
        $product = Product::create([
            "name" => $validated["name"],
            "description" => $validated["description"],
            "price" => $validated["price"],
            "stock" => $validated["stock"],
            "purchase_count" => 0,
            "category_id" => $validated["category_id"]
        ]);

        if ($request->hasFile("image")) {
            $image = $request->file("image");
            $extension = $image->getClientOriginalExtension();
            Storage::disk("public")->putFileAs("/uploads/products", $image, "$product->id.$extension");
            $product->update(["extension" => $extension]);
        }

        $extensions = [];
        if ($request->hasFile("additional_images")) {
            foreach ($request->file("additional_images") as $index => $additionalImage) {
                $ext = $additionalImage->getClientOriginalExtension();
                Storage::disk("public")->putFileAs("/uploads/products", $additionalImage, "$product->id-$index.$ext");
                $extensions[] = $ext;
            }
        }

        $product->update([
            "extension2" => $extensions[0] ?? null,
            "extension3" => $extensions[1] ?? null,
            "extension4" => $extensions[2] ?? null,
            "extension5" => $extensions[3] ?? null,
        ]);

        return $this->Created($product);
    }
    
    public function update(Request $request, Product $product)
    {
        $validator = validator()->make($request->all(), [
            "name" => "sometimes|max:255|unique:products,name,$product->id|string",
            "description" => "sometimes|max:2000|string",
            "price" => "sometimes|numeric|max:999999999|min:0",
            "image" => "sometimes|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096",
            "additional_images" => "nullable|array|max:4",
            "additional_images.*" => "nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096",
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
            Storage::disk("public")->putFileAs("/uploads/products", $request->file("image"), "{$product->id}.$extension");
            $validated['extension'] = $extension;
        }

        $extensions = [];
        if ($request->hasFile("additional_images")) {
            for ($i = 0; $i < 4; $i++) {
                $oldAdditionalImagePath = "/uploads/products/{$product->id}-$i.{$product["extension" . ($i + 2)]}";
                if (Storage::disk('public')->exists($oldAdditionalImagePath)) {
                    Storage::disk('public')->delete($oldAdditionalImagePath);
                }
            }

            foreach ($request->file("additional_images") as $index => $additionalImage) {
                $ext = $additionalImage->getClientOriginalExtension();
                Storage::disk("public")->putFileAs("/uploads/products", $additionalImage, "{$product->id}-$index.$ext");
                $extensions[] = $ext;
            }
        }

        $product->update(array_merge($validated, [
            "extension2" => $extensions[0] ?? $product->extension2,
            "extension3" => $extensions[1] ?? $product->extension3,
            "extension4" => $extensions[2] ?? $product->extension4,
            "extension5" => $extensions[3] ?? $product->extension5,
        ]));

        return $this->Ok($product, "Updated Successfully!");
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
