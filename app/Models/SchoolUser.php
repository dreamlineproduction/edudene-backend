<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolUser extends Model
{
    //
    protected $fillable = [
        'user_id',
        'school_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
