<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailChangeRequest extends Model
{
    //

    protected $fillable = [
        'user_id',
        'email',
        'new_email',
        'reason',
        'decline_text',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ]; 
}
