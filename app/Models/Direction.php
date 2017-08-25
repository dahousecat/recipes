<?php

namespace App\Models;

class Direction extends Model
{
    protected $fillable = [
    	'description'
    ];

    public $timestamps = false;

    /**
     * Get the recipe that owns the row.
     */
    public function recipe()
    {
        return $this->belongsTo('App\Models\Recipe');
    }

    public static function form()
    {
    	return [
    		'description' => ''
    	];
    }
}
