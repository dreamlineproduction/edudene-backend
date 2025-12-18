<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVerification extends Model
{
    //

    protected $fillable = [
        'user_id',
        'type',
        'id_type',
        'front_side_document',
        'front_side_document_url',
        'back_side_document',
        'back_side_document_url',
        'face_image',
        'face_image_url',        
        'status',
        'decline_text'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
