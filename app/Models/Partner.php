<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'type',
        'discount_title',
        'discount_type',
        'discount_amount',
        'discount_category',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'discount_amount' => 'float',
    ];

    /**
     * Get the school that owns this partner.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
