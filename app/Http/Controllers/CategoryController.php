<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Storage;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function index(){
        $categories = Category::with('products')->get();
        return $this->Ok($categories);
    }

    public function categoriesPurchaseCount(){
        $categories = Category::all();

        $categorySales = [];

        foreach ($categories as $category) {
            $totalSales = $category->products->sum('purchase_count');

            $categorySales[] = [
                'category' => $category->name,
                'total_sold' => $totalSales
            ];
        }

        return $this->Ok([
            'categories' => $categories,
            'sales_by_category' => $categorySales
        ]);
    }

    public function store(Request $request){
        $validator = validator()->make($request->all(), [
            "name" => "required|max:255|unique:categories|string",
            "image" => "required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096"
        ]);

        if ($validator->fails()){
            return $this->BadRequest($validator);
        }
        $validated = $validator->validated();
        $extension = $request->file("image")->getClientOriginalExtension();
        $validated['extension'] = $extension;

        $category = Category::create($validated);

        Storage::disk("public")->putFileAs("/uploads/categories", $request->file("image"), "$category->id.$extension");

        return $this->Created($category);
    }

    public function update(Request $request, Category $category)
    {
        $validator = validator()->make($request->all(), [
            "name" => "sometimes|max:255|unique:categories,name,$category->id|string",
            "image" => "sometimes|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096"
        ]);

        if ($validator->fails()) {
            return $this->BadRequest($validator);
        }

        $validated = $validator->validated();

        if ($request->hasFile("image")) {
            $oldImagePath = "/uploads/categories/{$category->id}.{$category->extension}";
            if (Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }

            $extension = $request->file("image")->getClientOriginalExtension();
            $validated['extension'] = $extension;
            Storage::disk("public")->putFileAs("/uploads/categories", $request->file("image"), "{$category->id}.$extension");
        }
        $category->update($validated);

        return $this->Ok($category, "Updated!");
    }

    
    public function destroy(Request $request, $categoryId)
    {
        $category = Category::find($categoryId);

        if (!$category) {
            return $this->NotFound("Category not found!");
        }

        $category->delete();

        return $this->Ok(null, "Category deleted successfully!");
    }

    public function show(Category $category){
        $category->products;

        return $this->Ok($category,"Retrieved!");
    }
}