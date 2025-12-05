<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    //

    protected $fillable = [
        'faq_section_id',
        'title',
        'description',
        'status',
        'is_home'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function section()
    {
        return $this->belongsTo(FaqSection::class,'faq_section_id');
    }
}


