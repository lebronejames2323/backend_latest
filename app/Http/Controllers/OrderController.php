<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function getRecentOrders()
    {
        $orders = Order::with(['products', 'user.profile'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        if ($orders->isEmpty()) {
            return $this->Ok([ 'recent_orders' => [], 'last_month_orders_count' => 0 ], "No orders found.");
        }

        foreach ($orders as $order) {
            $order->timeAgo = $order->created_at->diffForHumans();
        }

        $lastMonthOrdersCount = Order::where('created_at', '>=', now()->subMonth())->count();

        return $this->Ok([ 'recent_orders' => $orders, 'last_month_orders_count' => $lastMonthOrdersCount ], "Orders retrieved successfully.");
    }


    public function getAllOrders(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $status = $request->input('status');
        $dateRange = $request->input('date_range');
        $search = $request->input('search');

        $query = Order::withTrashed()->with([
            'products' => function ($query) {
                $query->withTrashed();
            },
            'user.profile'
        ])->orderBy('created_at', 'desc');

        if (!empty($status)) {
            $query->where('order_status', $status);
        }

        if (!empty($dateRange)) {
            if ($dateRange === "today") {
                $query->whereDate('created_at', now()->toDateString());
            } elseif ($dateRange !== "all_time") {
                $query->where('created_at', '>=', now()->subDays($dateRange));
            }
        }

        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('order_id', 'ILIKE', '%' . $search . '%')
                    ->orWhere('payment_method', 'ILIKE', '%' . $search . '%')
                    ->orWhere('full_name', 'ILIKE', '%' . $search . '%')
                    ->orWhereHas('products', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'ILIKE', '%' . $search . '%');
                    });
            });
        }

        $orders = $query->paginate($perPage);

        return response()->json([
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        ]);
    }


    public function index()
    {
        $userId = Auth::id();

        if (!$userId) {
            return $this->Unauthorized("Unauthorized access.");
        }

        $orders = Order::with(['products' => function ($query) {
            $query->withTrashed();
        }])->where('user_id', $userId)->orderBy('created_at', 'desc')->get();

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
        Log::info('Place Order Request:', $request->all());
        $validator = validator()->make($request->all(), [
            "products" => "required|array",
            "products.*" => "array",
            "products.*.id" => "required|exists:products,id",
            "products.*.quantity" => "required|min:1|max:1000000|int",
            "delivery_address" => "required|string|max:255",
            "payment_method" => "required|string|max:50",
            "full_name" => "required|string|max:255",
            "phone_number" => "required|string|max:20"
        ]);

        if ($validator->fails()){
            Log::warning('Validation Failed:', $validator->errors()->toArray());
            return $this->BadRequest($validator);
        }

        $order = $request->user()->orders()->create([
            'order_id' => strtoupper(Str::random(10)),
            'order_status' => 'Order Placed',
            'delivery_address' => $request->delivery_address,
            'payment_method' => $request->payment_method,
            'full_name' => $request->full_name,
            'phone_number' => $request->phone_number
        ] + $validator->validated());


        $items = [];
        $products = Product::all();

        foreach($request->products as $product){
            $p = $products->where("id", $product["id"])->first();

            if ($p->stock <= 0) {
                return response()->json(["message" => "$p->name is out of stock."], 400);
            }

            if ($product["quantity"] > $p->stock) {
                return response()->json(["message" => "Only {$p->stock} units of {$p->name} is available."], 400);
            }

            $items[$product["id"]] = ["price" => $p->price, "quantity" => $product["quantity"]];
            
            $p->stock -= $product["quantity"];
            $p->purchase_count += $product["quantity"];
            $p->save();
        }

        Log::info('Order Items:', $items);

        $order->products()->sync($items);

        Log::info('Order Finalized:', $order->load('products')->toArray());

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

    public function cancelOrder(Request $request, string $orderId) {
        $order = $request->user()->orders()->where('id', $orderId)->first();

        if (!$order) {
            return $this->NotFound("Order not found or does not belong to you!");
        }

        $order->update(['order_status' => 'Order Canceled']);

        foreach ($order->products as $product) {
            $product->stock += $product->pivot->quantity;
            $product->purchase_count -= $product->pivot->quantity;
            $product->save();
        }

        $order->delete();

        return $this->Ok(null, "Order has been canceled successfully!");
    }

}