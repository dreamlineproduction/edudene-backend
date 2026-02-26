<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    //
    protected $fillable = [
        'order_id',
        'user_id',
        'school_id',
        'tutor_id',
        'item_type',
        'item_id',
        'model_name',
        'title',
        'tutor_revenue',
        'school_revenue',
        'admin_revenue',
        'vat_rate',
        'vat_amount',
        'price',
        'discount_price',
        'quantity',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
