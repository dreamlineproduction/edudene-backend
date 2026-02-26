<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorSubSubCategory extends Model
{
    protected $table = 'tutor_sub_sub_category';

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

    public function subSubCategory()
    {
        return $this->belongsTo(SubSubCategory::class, 'category_id');
    }
}
