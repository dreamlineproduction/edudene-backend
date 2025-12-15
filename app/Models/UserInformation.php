<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInformation extends Model
{
    //
    protected $table = 'user_informations';

    protected $fillable = [
        'user_id',
        'phone_number',
        'about',
        'gender',
        'date_of_birth',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'zip',
        'country',
        'id_type',
        'front_side_document',
        'front_side_document_url',
        'back_side_document',
        'back_side_document_url',
        'face_image',
        'face_image_url',
        'x_url',
        'linkedin_url',
        'instagram_url',
        'facebook_url',
        'youtube_url',
        'github_url',
        'tiktok_url',
        'found_us',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
