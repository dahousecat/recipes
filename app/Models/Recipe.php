<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Ingredient;

class Recipe extends Model
{
    protected $fillable = [
    	'name', 'description', 'image'
    ];

    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class);
    }

    public function directions()
    {
    	return $this->hasMany(RecipeDirection::class);
    }

    public static function form()
    {
        return [
            'name' => '',
            'image' => '',
            'description' => '',
            'rows' => [
                //Row::form()
            ],
            'directions' => [
                RecipeDirection::form()
            ]
        ];
    }
}
