<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Recipe extends Model
{
    protected $fillable = [
    	'name',
        'description',
        'image',
        'portions',
        'citation',
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
    	return $this->hasMany(Direction::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function getScore() {
        return $this->votes()->sum('score');
    }

    public function likes() {
        return $this->votes()->where('score', 1)->count();
    }

    public function dislikes() {
        return $this->votes()->where('score', -1)->count();
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

        // Get recipes sorted by given attribute amount per 100g
        $query = DB::table('recipes as r')
            ->selectRaw("r.id, r.`name`, SUM((a.`value` / 100) * o.weight) / SUM(o.weight) * 100 as val, r.image, r.portions")
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

//        DB::enableQueryLog();

        $recipes = $query
            ->limit($limit)
            ->get()->toArray();

//        print_r(DB::getQueryLog());
//        die();

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
            'citation' => '',
            'rows' => [
                //Row::form()
            ],
            'directions' => [
                Direction::form()
            ]
        ];
    }
}
