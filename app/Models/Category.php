<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
	
    protected $fillable = [
        'title',
        'slug',
        'status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class,'category_id');
    }
}
