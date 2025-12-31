<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    //
    protected $table = 'schools';

    protected $fillable = [
        'user_id',
        'school_name',
        'school_slug',
        'phone_number',
        'about_us',
        'registration_number',
        'year_of_registration',
        'license_type',
        'tax_details',
        'school_document',
        'school_document_url',
        'address_line_1',
        'address_line_2',
        'zip',
        'city',
        'state',
        'country',
        'website',
        'social_media',
        'stripe_email',
        'facebook',
        'instagram',
        'linkedin',
        'youtube',
        'x',
        'vimeo',
        'pinterest',
        'github',
        'logo',
        'logo_url'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function tutors()
    {
        return $this->belongsToMany(User::class, 'school_users');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'school_courses');
    }

    public function classes()
    {
        return $this->hasMany(Classes::class, 'school_id');
    }
}
