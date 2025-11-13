<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    //

    protected $fillable = [
        'chat_id', 
        'sender_id', 
        'receiver_id',
        'message', 
        'file_url', 
        'is_read'
    ];

    protected $with = ['sender'];
    
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

}
