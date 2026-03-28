<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolCategory extends Model
{
    protected $table = 'school_category';

	protected $fillable = [
        'school_id',
        'category_id',
    ];
}
