<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseAsset extends Model
{
    //

   protected $fillable = [
        'course_id',
        'type',
        'video_url',
        'video',
        'poster',
        'poster_url'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
