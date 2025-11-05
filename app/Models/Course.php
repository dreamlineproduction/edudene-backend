<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    //
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'short_description',
        'description',
        'level',
        'course_type_id',
        'category_id',
        'sub_category_id',
        'sub_sub_category_id',
        'country_id',
        'state_id',
        'status',
        'price',
        'discount_price',
        'timezone',
        'created_at',
        'updated_at',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class)->select('id','role_id','full_name');
    }
    
    public function courseType()
    {
        return $this->belongsTo(CourseType::class);
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }
    public function subSubCategory()
    {
        return $this->belongsTo(SubSubCategory::class);
    }

    public function courseOutcomes()
    {
        return $this->hasMany(CourseOutcome::class);
    }

    public function courseRequirements()
    {
        return $this->hasMany(CourseRequirement::class);
    }

    public function courseSeo()
    {
        return $this->hasOne(CourseSeo::class);
    }

    public function courseChapters()
    {
        return $this->hasMany(CourseChapter::class);
    }

    public function courseAsset(){
        return $this->hasOne(CourseAsset::class);
    }

    protected $hidden   = [
        'created_at',
        'updated_at',
    ];
}
