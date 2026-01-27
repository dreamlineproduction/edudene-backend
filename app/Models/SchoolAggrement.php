<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolAggrement extends Model
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

    
    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
