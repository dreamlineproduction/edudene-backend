<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCategory extends Model
{
    //
    protected $table = 'user_category';

    protected $fillable = [
        'user_id',
        'category_id',
    ];

     protected $hidden = [
        'created_at',
        'updated_at',
    ];
    
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

   
}
