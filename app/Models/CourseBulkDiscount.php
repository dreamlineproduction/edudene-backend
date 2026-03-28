<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseBulkDiscount extends Model
{
    use HasFactory;

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

    /**
     * Get the course that owns the bulk discount.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Get the owner (school or tutor) of the bulk discount.
     */
    public function owner()
    {
        return $this->morphTo();
    }
}
