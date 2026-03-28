<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassBulkDiscount extends Model
{
    protected $fillable = [
        'title',
        'text',
        'owner_id',
        'owner_type',
        'min_quantity',
        'max_quantity',
        'discount_percentage',
        'status',
    ];

    protected $casts = [
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'discount_percentage' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
