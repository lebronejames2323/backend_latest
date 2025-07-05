<?php

namespace App\Http\Controllers;

use App\Models\Variation;
use Illuminate\Http\Request;

class VariationController extends Controller
{
    public function index()
    {
        $variations = Variation::with('product')->get();
        return $this->Ok($variations, "Variations retrieved successfully!");
    }

    public function indexPagination(Request $request)
    {
        $variations = Variation::with('product')
            ->orderBy('product_id')
            ->paginate(10);

        return $this->Ok($variations, "Variations retrieved successfully!");
    }

    public function store(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'variation_name' => 'required|string|max:255',
            'variation_price' => 'nullable|numeric',
            'variation_stock' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->BadRequest($validator);
        }

        $variation = Variation::create($validator->validated());

        return $this->Created($variation, "Variation created successfully!");
    }

    public function update(Request $request, $id)
    {
        $validator = validator()->make($request->all(), [
            'product_id' => 'sometimes|required|exists:products,id',
            'variation_name' => 'sometimes|required|string|max:255',
            'variation_price' => 'sometimes|numeric',
            'variation_stock' => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->BadRequest($validator);
        }

        $variation = Variation::find($id);

        if (!$variation) {
            return $this->NotFound("Variation not found.");
        }

        $variation->update($validator->validated());

        return $this->Ok($variation, "Variation updated successfully!");
    }

    public function destroy($id)
    {
        $variation = Variation::find($id);

        if (!$variation) {
            return $this->NotFound("Variation not found.");
        }

        $variation->delete();

        return $this->Ok(null, "Variation deleted successfully!");
    }
}