<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{

    public function index()
    {
        try {
            $userId = Auth::id();
    
            if (!$userId) {
                return $this->Unauthorized("Unauthorized access.");
            }
    
            $orders = Order::with('products')->where('user_id', $userId)->get();
    
            if ($orders->isEmpty()) {
                return $this->Ok([], "No orders found for this user.");
            }
    
            return $this->Ok($orders, "Orders retrieved successfully.");
        } catch (\Exception $e) {
            \Log::error('Order fetching error: ' . $e->getMessage());
            return $this->BadRequest(null, "Something went wrong.");
        }
    }

    public function show(Order $order)
{
    $order->load('products');
    return $this->Ok($order, "Orders retrieved with products.");
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
        
        $order = $request->user()->orders()->create($validator->validated());

        $items = [];
        $products = Product::all();

        foreach($request->products as $product){
            $p = $products->where("id", $product["id"])->first();
            $items[$product["id"]] = ["price" => $p->price, 
            "quantity" => $product["quantity"],
        ];

        $p->stock = $p->stock - $product["quantity"];
        $p->save();
        }

        $order->products()->sync($items);

        $order->products;

        return $this->Created($order, "Order has been created!");
    }
}