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



    protected static function boot()
    {
        parent::boot();

        static::created(function ($coupon) {
            if (empty($coupon->batch_number)) {
                $coupon->batch_number = 'BATCH-' . date('Ymd') . '-' . $coupon->id. strtoupper(Str::random(5));
                $coupon->save();
            }
        });
    }
}
