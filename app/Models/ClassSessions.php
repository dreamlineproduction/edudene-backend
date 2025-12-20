<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSessions extends Model
{
    protected $fillable = [
        'class_id',
        'school_id',
        'tutor_id',
        'start_date',
        'start_time',
        'end_time',
        'topic',
        'timezone',
        'is_leave',
        'created_at',
        'updated_at',
    ];


    protected $hidden = [
        'created_at',
        'updated_at', 
    ];
}
