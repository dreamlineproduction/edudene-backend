<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    //
    protected $fillable  = [
        'contact_topic_id',
        'name',
        'email',
        'subject',
        'message',
        'attachment',
        'attachment_url'
    ];

     public function topic() {
        return $this->belongsTo(ContactTopic::class);
    }
}
