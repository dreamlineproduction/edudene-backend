<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectRequest extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'sub_category_id',
        'sub_sub_category_id',
        'subject',        
        'status',
        'reason'
    ];
    
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    public function subSubCategory()
    {
        return $this->belongsTo(SubSubCategory::class, 'sub_sub_category_id');
    }

	public function user()
    {
        return $this->belongsTo(User::class);
    }
}
