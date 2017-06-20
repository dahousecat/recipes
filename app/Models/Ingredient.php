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
        'user_id',
    ];

    use SoftDeletes;

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
        return $this->hasMany('App\Models\Attribute');
    }

    /**
     * Return a json array of this ingredients units
     */
    public function jsonUnits() {
        $units = [];
        foreach($this->units as $unit) {
            $units[$unit->id] = $unit->name;
        }
        return json_encode($units);
    }

    /**
     * Return a json array of the ids of this ingredients units
     */
    public function jsonUnitIds() {
        $units = [];
        foreach($this->units as $unit) {
            $units[] = $unit->id;
        }
        return json_encode($units);
    }

    public static function form()
    {
        return [
            'name' => '',
            'image' => '',
            'description' => '',
            'units' => [
                Unit::form()
            ],
            'attributes' => [
                Attribute::form(),
            ]
        ];
    }

}
