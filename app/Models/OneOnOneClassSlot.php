<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OneOnOneClassSlot extends Model
{
    protected $fillable = [
		'tutor_id',
		'class_date',
		'start_time',
		'end_time',
		'is_free_trial',
		'is_active',
		'timezone'
	];
}
