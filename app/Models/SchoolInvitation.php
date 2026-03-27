<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolInvitation extends Model
{
    //

    protected $fillable = [
        'school_id',
        'user_id',
        'email',
        'token',
        'status'
    ];
}
