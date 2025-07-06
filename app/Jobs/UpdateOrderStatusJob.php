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
            $now = now();
            $orders = Order::where('order_status', '!=', 'Delivered')->get();

            foreach ($orders as $order) {
                $status = $order->order_status;
                $created = $order->created_at;
                $hoursSince = $created->diffInHours($now);

                $nextStatus = match (true) {
                    $status === 'Order Placed'     && $hoursSince >= 24 => 'Order Shipped',
                    $status === 'Order Shipped'    && $hoursSince >= 44 => 'Out for Delivery',
                    $status === 'Out for Delivery' && $hoursSince >= 48 => 'Delivered',
                    default => null,
                };

                if ($nextStatus) {
                    $order->order_status = $nextStatus;
                    $order->save();

                    $message = match($nextStatus) {
                        'Order Shipped'    => "Order shipped, your order is on its way!",
                        'Out for Delivery' => "Your order is now out for delivery and arriving soon.",
                        'Delivered'        => "Your order has been delivered.",
                    };

                    Notification::create([
                        'user_id' => $order->user_id,
                        'title'   => "Order #{$order->order_id}",
                        'message' => $message,
                    ]);

                }
            }
        } catch (\Exception $e) {
            Log::error("Order status job failed: " . $e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }

}