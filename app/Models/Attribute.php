<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ingredient_id',
        'value',
        'attribute_type_id',
    ];

    /**
     * The ingredient that has this attribute.
     */
    public function ingredient()
    {
        return $this->belongsTo('App\Models\Ingredient');
    }

    /**
     * The type of this attribute.
     */
    public function attributeType()
    {
        return $this->belongsTo('App\Models\AttributeType');
    }

    public function loadByIngredient($ingredient_id) {
        return DB::table('attributes')->where('ingredient_id', '=', $ingredient_id)->get();
    }

    public static function form()
    {
        return [
            'name' => '',
        ];
    }

    public static function deleteMany($ids) {
        foreach($ids as $id) {
            $attribute = static::find($id);
            if($attribute) {
                $attribute->delete();
            }
        }
    }
}
