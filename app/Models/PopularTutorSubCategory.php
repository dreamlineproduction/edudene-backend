<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PopularTutorSubCategory extends Model
{
    protected $fillable = [
        'tutor_id',
        'sub_category_id',
        'sort_order',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function tutor()
    {
        return $this->belongsTo(Tutor::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }
}
