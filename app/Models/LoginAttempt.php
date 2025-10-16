<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    //
    protected $fillable = [
        'user_id',
        'ip_address', 
        'email',
        'attempt_count',
        'timezone',
        'locked_datetime'
    ];
    
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
