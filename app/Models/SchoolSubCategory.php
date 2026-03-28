<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSubCategory extends Model
{
    protected $table = 'school_sub_category';

	protected $fillable = [
        'school_id',
        'category_id',
    ];

	protected $hidden = [
        'created_at',
        'updated_at',
    ];


	public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

	public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'category_id');
    }

}
