<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Ingredient;
use Illuminate\Support\Facades\DB;

class Recipe extends Model
{
    protected $fillable = [
    	'name',
        'description',
        'image'
    ];

    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function rows()
    {
        return $this->hasMany(Row::class);
    }

    public function directions()
    {
    	return $this->hasMany(RecipeDirection::class);
    }

    public function rowIds() {

        return $this->rows()->pluck('id')->toArray();
    }

    public function directionIds()
    {
        return $this->directions()->pluck('id')->toArray();
    }

    public static function recipesSortedByAttribute($attributeSafeName, $limit = 10) {

        $attributeSafeName = DB::connection()->getPdo()->quote($attributeSafeName);

//        $attributeSafeName .= '; drop table users;';
//        die($attributeSafeName);

        $recipes = DB::table('recipes as r')
//            ->select(DB::raw("r.id, r.`name`, SUM((a.`value` / 100) * o.weight) as :name", [':name' => $attributeSafeName]))
//            ->setBindings([$attributeSafeName])
            ->selectRaw("r.id, r.`name`, SUM((a.`value` / 100) * o.weight) as ?", [$attributeSafeName])
            ->leftJoin('rows as o',             'o.recipe_id',      '=', 'r.id')
            ->leftJoin('ingredients as i',      'i.id',             '=', 'o.ingredient_id')
            ->leftJoin('attributes as a',       'a.ingredient_id',  '=', 'i.id')
            ->leftJoin('attribute_types as t',  't.id',             '=', 'a.attribute_type_id')
            ->where('t.safe_name', $attributeSafeName)
            ->groupBy('r.id')
            ->orderBy($attributeSafeName, 'desc')
            ->limit($limit)
            ->toSql();
//            ->get();

        die($recipes);

        return $recipes;

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
