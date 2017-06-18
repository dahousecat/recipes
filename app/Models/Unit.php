<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\EnumOptions;
use DB;

class Unit extends Model
{
    use EnumOptions;

    protected $fillable = [
        'name',
    ];

    public $timestamps = false;

    /**
     * The ingredients that belong to the unit.
     */
    public function ingredients()
    {
        return $this->belongsToMany('App\Models\Ingredient');
    }

    /**
     * Return an array of possible types of unit
     */
    public static function getTypes() {
        return static::enumOptions('type');
    }

    /**
     * Load all units of a certain type(s)
     */
    public static function loadByType($type) {
        $type = is_array($type) ? $type : [$type];
        return self::whereIn('type', $type)->get();
    }

    public static function getAllKeyed() {
        $units = [];
        foreach(self::all() as $unit) {
            $units[$unit->id] = $unit->name;
        }
        return $units;
    }
}
