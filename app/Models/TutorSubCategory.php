<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorSubCategory extends Model
{
    protected $table = 'tutor_sub_category';

    protected $fillable = [
        'tutor_id',
        'category_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'category_id');
    }
}
