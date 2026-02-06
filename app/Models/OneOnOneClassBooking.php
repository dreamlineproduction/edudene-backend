<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OneOnOneClassBooking extends Model
{
    protected $fillable = [
		'slot_id',
		'student_id',
		'status',
		'booked_at',
		'timezone',		
	];
}
