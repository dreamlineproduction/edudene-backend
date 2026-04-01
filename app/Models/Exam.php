<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Exam extends Model
{
    protected $fillable = [
		'class_id',
		'enable',
		'no_of_questions',
		'total_exam_marks',
		'min_pass_marks',
		'duration',
		'retake_fee',
		'expiry_date',
		'school_id'
	];

	protected $appends = ['formatted_expiry_date'];

	public function class() {
		return $this->belongsTo(Classes::class);		
	}

	public function getFormattedExpiryDateAttribute()
	{
	    return $this->expiry_date
	        ? Carbon::parse($this->expiry_date)->format('d M Y')
	        : null;
	}

}
