<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryLevelFour extends Model
{

    protected $fillable = [
        'category_id',
        'sub_category_id',
        'sub_sub_category_id',
        'title',
        'slug',
        'status'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function categoryLevelTwo()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function categoryLevelThree()
    {
        return $this->belongsTo(SubSubCategory::class);
    }

    protected $hidden = [
        'created_at', 
        'updated_at'
    ];
}
