<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Classes extends Model
{
    protected $appends = ['formatted_start_date', 'formatted_end_date'];

    protected $fillable = [
        'class_type_id',
        'category_id',
        'sub_category_id',
        'sub_sub_category_id',
        'category_level_four_id',
        'tutor_id',
        'school_id',
        'start_date',
        'end_date',
        'duration',
        'price',
        'status',
        'decline_text',
        'description',
        'cover_image',
        'cover_image_url',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
       'updated_at', 
    ];

    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function enrollments()
    {
        return $this->hasMany(SchoolClassBooking::class, 'class_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function sub_category()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    public function sub_sub_category()
    {
        return $this->belongsTo(SubSubCategory::class, 'sub_sub_category_id');
    }

    public function category_level_four()
    {
        return $this->belongsTo(CategoryLevelFour::class, 'category_level_four_id');
    }

    public function class_type()
    {
        return $this->belongsTo(ClassType::class, 'class_type_id');
    }

    public function class_sessions()
    {
        return $this->hasMany(ClassSessions::class, 'class_id');
    }

    public function exam()
    {
        return $this->hasOne(Exam::class,'class_id');
    }

	public function getFormattedStartDateAttribute()
	{
		return $this->start_date 
			? Carbon::parse($this->start_date)->format('d/m/Y') 
			: null;
	}

	public function getFormattedEndDateAttribute()
	{
		return $this->end_date 
			? Carbon::parse($this->end_date)->format('d/m/Y') 
			: null;
	}

}
