<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolCategoryLevelFour extends Model
{
    protected $table = 'school_category_level_four';

	protected $fillable = [
        'school_id',
        'category_id',
    ];

	protected $hidden = [
        'created_at',
        'updated_at',
    ];

	public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function categoryLevelFour()
    {
        return $this->belongsTo(CategoryLevelFour::class, 'category_id');
    }
}
