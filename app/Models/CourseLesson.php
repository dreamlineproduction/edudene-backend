<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseLesson extends Model
{
    //
    protected $appends = ['duration_formatted'];
    
    protected $fillable = [
		'summary',
        'course_id',
        'duration',
        'course_chapter_id',
        'is_free_lesson',
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

    public function getDurationFormattedAttribute()
    {
        $seconds = (int) $this->duration;

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m {$secs}s";
    }

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
