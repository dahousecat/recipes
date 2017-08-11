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

    public static function recipesSortedByAttribute($attributeSafeName, $limit = 10, $recipe_ids = null)
    {
        $query = DB::table('recipes as r')
            ->selectRaw("r.id, r.`name`, SUM((a.`value` / 100) * o.weight) as val, r.image, r.portions")
            ->leftJoin('rows as o',             'o.recipe_id',      '=', 'r.id')
            ->leftJoin('ingredients as i',      'i.id',             '=', 'o.ingredient_id')
            ->leftJoin('attributes as a',       'a.ingredient_id',  '=', 'i.id')
            ->leftJoin('attribute_types as t',  't.id',             '=', 'a.attribute_type_id')
            ->where('t.safe_name', $attributeSafeName)
            ->groupBy('r.id')
            ->orderBy('val', 'desc');

        if(!empty($recipe_ids)) {
            $query->whereIn('r.id', $recipe_ids);
        }

        $recipes = $query
            ->limit($limit)
            ->get()->toArray();

        return $recipes;

    }

    public static function withIngredients($ingredient_ids, $fields = ['r.id', 'r.name', 'r.image', 'r.portions'])
    {
        $query = DB::table('recipes as r')
            ->leftJoin('rows as o', 'o.recipe_id', '=', 'r.id')
            ->whereIn('o.ingredient_id', $ingredient_ids)
            ->groupBy('o.recipe_id')
            ->havingRaw('COUNT(*) = ' . count($ingredient_ids));

        if(count($fields) == 1) {
            $recipes = $query->pluck($fields[0]);
        } else {
            $recipes = $query->get($fields);
        }

        return $recipes;

    }

    /**
     * Return a list of fields that recipes can be sorted by.
     */
    public static function sortableBy()
    {
        $sortableBy = [];
        $sortableBy['recipe_name'] = 'Name';
        $attributeTypes =  AttributeType::pluck('name', 'safe_name')->toArray();
        $sortableBy = array_merge($sortableBy, $attributeTypes);
        return $sortableBy;
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
