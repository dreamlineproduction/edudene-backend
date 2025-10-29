<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseChapter extends Model
{
    //

    protected $fillable = [
        'course_id',
        'title',
        'price',
        'discount_price',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
