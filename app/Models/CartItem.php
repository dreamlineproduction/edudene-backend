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
        'model_name',
        'title',
        'price',
        'discount_price',
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

    public function item()
    {
        return $this->morphTo(
            name: 'item',
            type: 'model_name',
            id: 'item_id'
        );
    }
}
