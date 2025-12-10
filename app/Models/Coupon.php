<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Coupon extends Model
{
    //

    protected $fillable = [
        'title',
        'type',
        'amount',
        'percentage',
        'validity',
        'batch_number',
        'is_redeem',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
