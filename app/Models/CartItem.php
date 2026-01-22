<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    //

    protected $fillable = [
        'cart_id',
        'item_type',
        'item_id',
        'title',
        'price',
        'qty',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];


    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
}
