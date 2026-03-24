<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyCourse extends Model
{
    //

    protected $fillable  = [
        'course_id',
        'user_id',
        'chapter_id',
        'is_partial',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected $hidden   = [
        'created_at',
        'updated_at',
    ];
}
