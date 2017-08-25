<?php

namespace App\Models;

class Vote extends Model
{
    protected $fillable = [
        'recipe_id',
        'user_id',
        'score',
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
