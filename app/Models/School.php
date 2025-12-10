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
        'phone_number',
        'about_us',
        'registration_number',
        'year_of_registration',
        'license_type',
        'tax_details',
        'address_line_1',
        'address_line_2',
        'zip',
        'city',
        'state',
        'country',
        'website',
        'social_media',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
