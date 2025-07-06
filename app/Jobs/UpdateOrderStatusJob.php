<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\Order;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;

class UpdateOrderStatusJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $orders = Order::where('order_status', '!=', 'Delivered')->get();
            Log::info('Order status job started at ' . now());

            foreach ($orders as $order) {
                Log::info("Processing Order {$order->id} - Current Status: {$order->order_status}");

                $nextStatus = match ($order->order_status) {
                    'Order Placed'     => 'Packing Order',
                    'Packing Order'    => 'Order Shipped',
                    'Order Shipped'    => 'Out for Delivery',
                    'Out for Delivery' => 'Delivered',
                    default            => null,
                };

                if ($nextStatus) {
                    $order->order_status = $nextStatus;
                    $order->save();

                    $message = match($nextStatus) {
                        'Packing Order'    => "Your order is being packed and prepped for shipping.",
                        'Order Shipped'    => "Order shipped, your order is on its way!",
                        'Out for Delivery' => "Your order is now out for delivery and arriving soon.",
                        'Delivered'        => "Your order has been delivered.",
                    };

                    Notification::create([
                        'user_id' => $order->user_id,
                        'title'   => "Order #{$order->order_id}",
                        'message' => $message,
                    ]);

                    Log::info("→ Order {$order->id} updated to: {$nextStatus}");
                }
            }
        } catch (\Exception $e) {
            Log::error("Order status job failed: " . $e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }
}