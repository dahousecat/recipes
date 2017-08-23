<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{

    protected $fillable = [
        'name',
        'description',
        'image',
        'weight_one',
        'weight_one_cup',
        'weight_one_cm',
        'default_unit_id',
        'user_id',
    ];

//    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The units that belong to the ingredient.
     */
    public function units()
    {
        return $this->belongsToMany('App\Models\Unit');
    }

    /**
     * The attributes that belong to the ingredient.
     */
    public function attributes()
    {
        $attributes = $this->hasMany('App\Models\Attribute');
        return $attributes;
    }

    /**
     * The rows with this ingredient
     */
    public function rows()
    {
        $attributes = $this->hasMany('App\Models\Row');
        return $attributes;
    }

    /**
     * Return an array of this ingredients attributes ids
     *
     * @return mixed
     */
    public function attributeIds()
    {
        return $this->attributes()->pluck('id')->toArray();
    }

    /**
     * Return a collection of recipes that use this ingredient
     */
    public function recipes()
    {
        // This doesn't appear to work on many to many relationships
//        return $this->hasManyThrough('App\Models\Recipe', 'App\Models\Row');

        $recipes = array();
        foreach ($this->rows as $row) {
            $recipes[] = $row->recipe;
        }
        return \Illuminate\Database\Eloquent\Collection::make($recipes);

    }

    /**
     * Return a json array of this ingredients units
     */
    public function jsonUnits()
    {
        $units = [];
        foreach($this->units as $unit) {
            $units[$unit->id] = $unit->name;
        }
        return json_encode($units);
    }

    /**
     * Return a json array of the ids of this ingredients units
     */
    public function jsonUnitIds()
    {
        $units = [];
        foreach($this->units as $unit) {
            $units[] = $unit->id;
        }
        return json_encode($units);
    }

    public static function loadByName($name)
    {
        return self::where('name', $name)->first();
    }

    public static function form()
    {
        return [
            'name' => '',
            //'image' => '',
            //'description' => '',
            'weight_one' => '',
            'weight_one_cup' => '',
            'weight_one_cm' => '',
            'default_unit_id' => '',
            'units' => [],
            'nutrients' => []
        ];
    }
}
