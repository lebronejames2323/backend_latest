<?php

namespace App\Jobs;

use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class UpdateOrderStatusJob implements ShouldQueue
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
        $orders = Order::where('order_status', '!=', 'Delivered')->get();
        Log::info('Order status job started at ' . now());

        foreach ($orders as $order) {
            $now = now();
            $created = $order->created_at;
            $daysPassed = $created->diffInDays($now);
            $hoursSinceDay2 = $created->copy()->addDays(2)->diffInHours($now);
            $hourNow = $now->hour;
            Log::info("User ID for Order {$order->id}: {$order->user_id}");

            if ($daysPassed === 1 && $order->order_status === 'Order Placed') {
                $order->order_status = 'Order Shipped';
            } elseif (
                $daysPassed === 2 &&
                $order->order_status === 'Order Shipped' &&
                $hoursSinceDay2 < 2 &&
                $hourNow >= 6 && $hourNow <= 20
            ) {
                $order->order_status = 'Out for Delivery';
            } elseif (
                $daysPassed === 2 &&
                $order->order_status === 'Out for Delivery' &&
                $hoursSinceDay2 >= 2 &&
                $hourNow >= 6 && $hourNow <= 20
            ) {
                $order->order_status = 'Delivered';
            }

            if ($order->isDirty('order_status')) {
                $order->save();
            }

            $message = match($order->order_status) {
                'Order Placed'     => "Thank you for your purchase! Your order has been placed.",
                'Packing Order'     => "Your order is being packed and prepped for shipping.",
                'Order Shipped'     => "Order shipped, your order is on its way!",
                'Out for Delivery'  => "Your order is now out for delivery and arriving soon.",
                'Delivered'         => "Your order has been delivered.",
            };

            Notification::create([
                'user_id' => $order->user_id,
                'title'   => "Order #{$order->order_id}",
                'message' => $message,
            ]);
        }
    }
}