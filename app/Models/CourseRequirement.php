<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseRequirement extends Model
{
    //

    protected $fillable = [
        'course_id',
        'title',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
