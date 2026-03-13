<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    //

    protected $fillable = [
        'user_id',
        'phone_number',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'zip',
        'country',
        'x_url',
        'facebook_url',
        'linkedin_url',
        'instagram_url',
        'youtube_url',
        'avatar',
        'avatar_url',       
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
