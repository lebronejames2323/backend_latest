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
}