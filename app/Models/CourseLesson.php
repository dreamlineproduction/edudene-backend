<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseLesson extends Model
{
    //

    protected $fillable = [
        'course_id',
        'course_chapter_id',
        'title',
        'type',
        'video_url',
        'video',
        'image',
        'image_url',
        'document',
        'created_at',
        'updated_at',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function courseChapter()
    {
        return $this->belongsTo(CourseChapter::class);
    }

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
