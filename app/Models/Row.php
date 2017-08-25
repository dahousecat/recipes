<?php

namespace App\Models;

class Row extends Model
{
    protected $fillable = [
        'recipe_id',
        'ingredient_id',
        'delta',
        'unit_id',
        'value',
        'weight',
    ];

    public $timestamps = false;

    /**
     * Get the ingredient for this row.
     */
    public function ingredient()
    {
        return $this->belongsTo('App\Models\Ingredient');
    }

    /**
     * Get the recipe that owns the row.
     */
    public function recipe()
    {
        return $this->belongsTo('App\Models\Recipe');
    }

    /**
     * Get the unit for the row
     */
    public function unit()
    {
        return $this->belongsTo('App\Models\Unit');
    }

    public static function form()
    {
        return [
            'ingredient' => '',
            'unit' => '',
            'amount' => '',
        ];
    }
}
