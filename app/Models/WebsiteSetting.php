<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteSetting extends Model
{
    //

    protected $fillable = [
        'title',
        'dark_logo',
        'dark_logo_url',
        'light_logo',
        'light_logo_url',
        'favicon_logo',
        'favicon_logo_url',
        'name',
        'keywords',
        'description',
        'author',
        'slogan',
        'system_email',
        'address',
        'phone_number',
        'agora_app_id',
        'agora_certificate',
        'ipinfo_token',
        'footer_text',
        'footer_link',
        'created_at',
        'updated_at',            
    ];

    protected $hidden = [ 
        'created_at',
        'updated_at',
    ];
}
