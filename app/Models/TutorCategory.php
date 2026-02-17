<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorCategory extends Model
{
    protected $table = 'tutor_category';

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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
