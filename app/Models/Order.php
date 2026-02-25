<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $fillable = [
        'user_id',
        'order_number',
        'payment_intent_id',
        'payment_method',
        'card_brand',
        'card_last4',
        'vat_rate',
        'vat_amount',
        'subtotal',
        'discount',
        'total',
        'pay_with',
        'status',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
