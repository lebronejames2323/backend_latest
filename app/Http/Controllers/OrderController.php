<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderController extends Controller
{

    public function getAllOrders()
    {
        $orders = Order::with(['products' => function ($query) {
            $query->withTrashed();
        }, 'user.profile'])->get();

        if ($orders->isEmpty()) {
            return $this->Ok([], "No orders found.");
        }

        return $this->Ok($orders, "Orders retrieved successfully.");
    }


    public function index()
    {
        $userId = Auth::id();

        if (!$userId) {
            return $this->Unauthorized("Unauthorized access.");
        }

        $orders = Order::with(['products' => function ($query) {
            $query->withTrashed();
        }])->where('user_id', $userId)->get();

        if ($orders->isEmpty()) {
            return $this->Ok([], "No orders found for this user.");
        }

        return $this->Ok($orders, "Orders retrieved successfully.");
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
        
        $order = $request->user()->orders()->create([
            'order_id' => strtoupper(Str::random(10)),
            'order_status' => 'Order Placed'
        ] + $validator->validated());

        $items = [];
        $products = Product::all();

        foreach($request->products as $product){
            $p = $products->where("id", $product["id"])->first();
            $items[$product["id"]] = ["price" => $p->price, 
            "quantity" => $product["quantity"]
        ];

        $p->stock = $p->stock - $product["quantity"];
        $p->save();
        }

        $order->products()->sync($items);

        $order->products;

        return $this->Created($order, "Order has been created!");
    }

    public function updateOrderStatus(Request $request, $orderId)
    {
        $validator = validator()->make($request->all(), [
            "order_status" => "required|string|max:255"
        ]);

        if ($validator->fails()) {
            return $this->BadRequest($validator);
        }

        $order = Order::find($orderId);
        if (!$order) {
            return $this->NotFound("Order not found.");
        }

        $order->update([
            'order_status' => $request->order_status
        ]);

        return $this->Ok($order, "Order status updated successfully.");
    }

}