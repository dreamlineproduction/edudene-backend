<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseChapter extends Model
{
    //
    protected $appends = ['discount_percent'];

    
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

	public function courseLessons()
    {
        return $this->hasMany(CourseLesson::class);
    }

    public function getDiscountPercentAttribute()
    {
        if (
            empty($this->price) ||
            empty($this->discount_price) ||
            $this->discount_price >= $this->price
        ) {
            return 0;
        }

        return round(
            (($this->price - $this->discount_price) / $this->price) * 100
        );
    }
}
