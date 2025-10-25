<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBillingInformation extends Model
{
    protected $table = 'user_billing_informations';

    protected $fillable = [
        'user_id',
        'address_type',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'zip',
        'country',
        'set_default',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
