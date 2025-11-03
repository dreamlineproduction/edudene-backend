<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    //
    protected $fillable = [
        'name', 'path', 'url', 'type', 'mime_type'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
