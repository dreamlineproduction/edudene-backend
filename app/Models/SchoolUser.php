<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolUser extends Model
{
    //
    protected $fillable = [
        'user_id',
        'school_id',
        'ip_agreement',
        'agreement_file',
        'agreement_file_url',
        'is_freelancer',
    ];
}
