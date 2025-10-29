<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseSeo extends Model
{
    
    protected $fillable = [
        'course_id',
        'meta_title',
        'meta_description',
        'meta_keyword',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
