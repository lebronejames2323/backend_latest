<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Variation extends Model
{
    use SoftDeletes;

    protected $fillable = ['product_id', "variation_name", "variation_price", "variation_stock"];

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function carts(){
        return $this->belongsToMany(Cart::class, 'cart_product')->withPivot('quantity', 'price');
    }
}