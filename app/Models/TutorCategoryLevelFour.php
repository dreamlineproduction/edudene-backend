<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorCategoryLevelFour extends Model
{
    protected $table = 'tutor_category_level_four';

    protected $fillable = [
        'tutor_id',
        'category_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function categoryLevelFour()
    {
        return $this->belongsTo(CategoryLevelFour::class, 'category_id');
    }
}
