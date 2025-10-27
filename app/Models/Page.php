<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    //

    protected $fillable = [
        'title',
        'slug',
        'description',
        'meta_title',
        'meta_description',
        'meta_keyword',
        'is_show',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
