<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    //

    protected $fillable = ['sender_id', 'receiver_id'];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }


    // Custom method to get the 'other' user in the chat
    public function otherUser(User $currentUser)
    {
        return $this->sender_id === $currentUser->id ? $this->receiver : $this->sender;
    }
}
