<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "name", "description", "price", "stock", "purchase_count", "category_id", "extension"
    ];

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function orders(){
        return $this->belongsToMany(Order::class)->withPivot("quantity", "price");
    }
    public function carts(){
        return $this->belongsToMany(Cart::class)->withPivot("quantity");
    }
    public function wishlists(){
        return $this->belongsToMany(Wishlist::class, 'wishlist_product')->withPivot('quantity');
    }
}
