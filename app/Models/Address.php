<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id', 'full_name', 'phone_number', 'region', 'province', 'city', 'barangay', 'postal_code', 'street_address',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
