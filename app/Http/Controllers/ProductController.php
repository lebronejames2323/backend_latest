<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Storage;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 12);
        $search = $request->input('search');
        $category = $request->input('category');

        $query = Product::with('category')->orderBy('created_at', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) ILIKE ?', ['%' . strtolower($search) . '%'])
                ->orWhereRaw('LOWER(description) ILIKE ?', ['%' . strtolower($search) . '%'])
                ->orWhereHas('category', function ($categoryQuery) use ($search) {
                    $categoryQuery->whereRaw('LOWER(name) ILIKE ?', ['%' . strtolower($search) . '%']);
                });
            });
        }

        if (!empty($category)) {
            $query->where('category_id', $category);
        }

        $products = $query->paginate($perPage);

        return response()->json([
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
        ]);
    }

    public function allProducts(){
        $products = Product::with("category")->get();
        return $this->Ok($products);
    }

    
    public function show($productId){
        $product = Product::with("category", "variations")->find($productId);

        if (!$product) {
            return response()->json(["message" => "Product not found"], 404);
        }

        $ratings = DB::table('review_product')
            ->select(DB::raw('AVG(star_rating) as average_rating'), DB::raw('COUNT(*) as total_reviews'))
            ->where('product_id', $productId)
            ->first();

        return response()->json([
            'data' => $product,
            'average_rating' => round($ratings->average_rating, 1) ?? 0,
            'total_reviews' => $ratings->total_reviews ?? 0
        ]);
    }

    public function featured(){
        $products = Product::with("category")
        ->where("stock", ">", 0)
        ->orderBy("purchase_count", "desc")
        ->take(8)
        ->get();
        return $this->Ok($products);
    }

    public function recommended(){
        $products = Product::with("category")
            ->where("stock", ">", 0)
            ->inRandomOrder()
            ->take(4)
            ->get();
            
        return $this->Ok($products);
    }

    public function getSalesData()
    {
        $sales = Product::selectRaw("TO_CHAR(created_at, 'YYYY-MM') as month, SUM(price * purchase_count) as monthly_revenue")
            ->groupBy("month")
            ->orderBy("month", "ASC")
            ->get();

        $totalRevenue = Product::selectRaw("SUM(price * purchase_count) as total_revenue")->first();
        $totalProductPrice = Product::selectRaw("SUM(price) as total_product_price")->first();

        Log::info('Monthly & Total Revenue Data:', [
            'monthly_sales' => $sales,
            'total_revenue' => $totalRevenue->total_revenue,
            'total_product_price' => $totalProductPrice->total_product_price
        ]);

        return response()->json([
            'monthly_sales' => $sales,
            'total_revenue' => $totalRevenue->total_revenue,
            'total_product_price' => $totalProductPrice->total_product_price
        ]);
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
