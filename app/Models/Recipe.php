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
        'image',
        'portions',
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

    public static function recipesSortedByAttribute($attributeSafeName, $limit = 10)
    {

        $recipes = DB::table('recipes as r')
            ->selectRaw("r.id, r.`name`, SUM((a.`value` / 100) * o.weight) as val", [$attributeSafeName])
            ->leftJoin('rows as o',             'o.recipe_id',      '=', 'r.id')
            ->leftJoin('ingredients as i',      'i.id',             '=', 'o.ingredient_id')
            ->leftJoin('attributes as a',       'a.ingredient_id',  '=', 'i.id')
            ->leftJoin('attribute_types as t',  't.id',             '=', 'a.attribute_type_id')
            ->where('t.safe_name', $attributeSafeName)
            ->groupBy('r.id')
            ->orderBy('val', 'desc')
            ->limit($limit)
            ->get()->toArray();

        return $recipes;

    }

    public static function form()
    {
        return [
            'name' => '',
            'image' => '',
            'description' => '',
            'portions' => '',
            'rows' => [
                //Row::form()
            ],
            'directions' => [
                RecipeDirection::form()
            ]
        ];
    }
}
