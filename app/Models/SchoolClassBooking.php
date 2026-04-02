<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClassBooking extends Model
{
    //

    protected $fillable = [
        'class_id',
        'school_id',
        'user_id',
        'booked_at',
        'timezone'
    ];

    protected $hidden = [
        'created_at','updated_at'
    ];
}
