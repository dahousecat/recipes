<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\EnumOptions;
use Illuminate\Support\Facades\DB;

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
     * The attributes with this unit.
     */
    public function attributes() {
        return $this->hasMany('App\Models\Attributes');
    }

    /**
     * Return an array of possible types of unit
     */
    public static function getTypes($ids = null) {
        if(is_null($ids)) {
            return static::enumOptions('type');
        } else {
            $ids = is_array($ids) ? $ids : [$ids];
            return DB::table('units')->whereIn('id', $ids)->distinct()->pluck('type')->toArray();
        }
    }

    /**
     * Load all units of a certain type(s)
     */
    public static function loadByType($type) {
        $type = is_array($type) ? $type : [$type];
        return self::whereIn('type', $type)->get();
    }

    public static function loadByName($name) {
        return self::where('name', $name)->first();
    }

    public static function getAllKeyed() {
        $units = [];
        foreach(self::all() as $unit) {
            $units[$unit->id] = $unit->name;
        }
        return $units;
    }

}
