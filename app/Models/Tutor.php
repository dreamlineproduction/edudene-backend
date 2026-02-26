<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tutor extends Model
{
    //

    protected $fillable = [ 
        'user_id',
        'phone_number',
        'year_of_experience',
        'about',
        'what_i_teach',
        'education',
        'language',
        'passing_year',
        'university',
        'highest_qualification',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'zip',
        'country',
        'x_url',
        'facebook_url',
        'linkedin_url',
        'avatar',
        'avatar_url',
        'police_certificate',
        'police_certificate_url',
        'experience_letter',
        'experience_letter_url',
        'qualification_certificate',
        'qualification_certificate_url',
        'video_type',
        'video_url',
        'video',
        'video_poster',
        'is_admin_verified',
        'is_house',
        'created_at',
        'updated_at',
		'enable_one_to_one',
		'enable_trainer',
		'enable_courses',
		'one_to_one_hourly_rate',
		'trainer_hourly_rate'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
            return $this->hasOneThrough(
                School::class,
                SchoolUser::class,
                'user_id',    // school_users.user_id
                'id',         // schools.id
                'user_id',    // tutors.user_id
                'school_id'   // school_users.school_id
            )->select([
                'schools.id',
                'schools.school_name',
                'schools.school_slug',
            ]);
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'user_id', 'user_id'); 
    }


    public function classes()
    {
        return $this->hasMany(Classes::class, 'tutor_id', 'user_id');
    }

    protected $hidden = [ 
        'created_at',
        'updated_at',
    ];

	protected $casts = [
		'enable_one_to_one' => 'boolean',
		'enable_trainer' => 'boolean',
		'enable_courses' => 'boolean',
	];
}
