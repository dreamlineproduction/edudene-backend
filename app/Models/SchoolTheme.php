<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolTheme extends Model
{
    //

    protected $fillable = [
        'school_id',

        'primary_color',
        'primary_hover_color',
        'primary_outline_color',
        'primary_outline_hover_color',

        'primary_text_color',
        'primary_hover_text_color',
        'primary_outline_text_color',
        'primary_outline_hover_text_color',

        'secondary_color',
        'secondary_hover_color',
        'secondary_outline_color',
        'secondary_outline_hover_color',

        'secondary_text_color',
        'secondary_hover_text_color',
        'secondary_outline_text_color',
        'secondary_outline_hover_text_color',

        'logo',
        'logo_url',
        'banner_image',
        'banner_image_url',
    ];

    protected $hidden = [
        'school_id',
        'created_at',
        'updated_at'
    ];
}
