<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PopularSubCategory extends Model
{
    protected $fillable = [
        'sub_category_id',
        'sort_order',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

	
}
