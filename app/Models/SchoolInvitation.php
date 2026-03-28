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


    public function school()
    {
        return $this->belongsTo(School::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    protected $hidden =[
        'created_at',
        'updated_at'
    ];
}
