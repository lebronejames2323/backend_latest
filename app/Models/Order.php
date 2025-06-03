<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['user_id', 'order_status', 'order_id', "delivery_address", "payment_method"];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            $order->order_id = strtoupper(Str::random(10));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot("quantity", "price");
    }
}
