<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserQualification extends Model
{
    //
    protected $table = 'user_qualifications';

    protected $fillable = [
        'user_id',
        'qualification_name',
        'institution_name',
        'completion_year',
        'is_show_profile',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
