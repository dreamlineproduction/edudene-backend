<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubSubCategory extends Model
{
    //
    protected $fillable = [
        'category_id',
        'sub_category_id',
        'title',
        'slug',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }
}
