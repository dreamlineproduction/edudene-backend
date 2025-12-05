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
        'police_certificate',
        'experience_letter',
        'qualification_certificate',
        'video_type',
        'video_url',
        'video',
        'video_poster',
        'is_admin_verified',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [ 
        'created_at',
        'updated_at',
    ];
}
