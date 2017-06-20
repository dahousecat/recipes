<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'unit',
    ];

    /**
     * The attributes that have this type.
     */
    public function attributes()
    {
        return $this->hasMany('App\Models\Attribute');
    }

    public static function loadByName($name) {
        return self::where('name', $name)->first();
    }

}
