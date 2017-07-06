<?php

namespace App\Http\Controllers;

use App\Models\AttributeType;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use App\Models\RecipeIngredient;
use App\Models\RecipeDirection;
use App\Models\Recipe;
use App\Models\User;
use App\Models\Row;
use File;

use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Document\Link;
use Neomerx\JsonApi\Encoder\Encoder;
use App\Models\Unit;

use App\Models\Api\UserSchema;

//class RecipeController extends Controller
class RecipeController extends JsonApiController
{
    public function __construct()
    {
    	$this->middleware('auth:api')
    		->except(['index', 'show']);

    	parent::__construct();
    }

    public function index()
    {
    	$recipes = Recipe::orderBy('created_at', 'desc')
//    		->get(['id', 'name', 'image']);
    		->get();

//        $recipes = Recipe::all();

        // Encode the model data for json:api consumption
//        $encoder = Encoder::instance($this->modelSchemaMappings, $this->encoderOptions);
//        $encodedData = $encoder->encodeData($recipes);
//        return response($encodedData)
//            ->header('Content-Type', 'application/json');

    	return response()
    		->json([
    			'recipes' => $recipes
    		]);
    }

    public function create()
    {
        $form = Recipe::form();
        $units = Unit::all()->toArray();
        $ingredients = Ingredient::select('id', 'default_unit_id', 'name', 'weight')
            ->with(['units' => function($query){
                $query->get(['unit_id', 'type', 'name']);
            }])
            ->limit(20)->get();
        $attributeTypes = AttributeType::all()->toArray();

    	return response()
    		->json([
    			'form' => $form,
                'ingredients' => $ingredients,
                'units' => $units,
                'attributeTypes' => $attributeTypes,
    		]);
    }

    public function store(Request $request)
    {

//        echo '<pre>';
//        print_r($request->all());
//        echo '</pre>';
//        die();

    	$this->validate($request, [
    		'name' => 'required|max:255',
    		'description' => 'max:3000',
    		'image' => 'image'
            ,
//    		'ingredients' => 'required|array|min:1',
//    		'ingredients.*.name' => 'required|max:255',
//    		'ingredients.*.qty' => 'required|max:255',

            'rows' => 'array|min:1',
            'rows.*.ingredient_id' => 'integer|exists:ingredients,id',
            'rows.*.delta' => 'required|integer|min:0',
            'rows.*.unit_id' => 'integer|exists:units,id',
            'rows.*.value' => 'required|numeric',

    		'directions' => 'array',
//    		'directions.*.description' => 'required|max:3000'
    	]);

    	$rows = [];
        foreach($request->rows as $row) {
            $rows[] = new Row($row);
        }

	    $directions = [];
        foreach($request->directions as $direction) {
            if(empty($direction->description)) {
                continue;
            }
            $directions[] = new RecipeDirection($direction);
        }

    	if(!$request->hasFile('image') && !$request->file('image')->isValid()) {
    		return abort(404, 'Image not uploaded!');
    	}

    	$filename = $this->getFileName($request->image);
    	$request->image->move(base_path('public/images'), $filename);

    	$recipe = new Recipe($request->only('name', 'description'));
    	$recipe->image = $filename;



    	$request->user()->recipes()
    		->save($recipe);

    	$recipe->rows()->saveMany($rows);

    	$recipe->directions()->saveMany($directions);

    	return response()
    	    ->json([
    	        'saved' => true,
    	        'id' => $recipe->id,
                'message' => 'You have successfully created recipe!'
    	    ]);
    }

    private function getFileName($file)
    {
    	return str_random(32).'.'.$file->extension();
    }

    public function show($id)
    {
        $recipe = Recipe::with(['user', 'rows.ingredient', 'rows.unit', 'directions'])
            ->findOrFail($id);

        return response()
            ->json([
                'recipe' => $recipe
            ]);
    }


    public function edit($id, Request $request)
    {
        $form = $request->user()->recipes()
            ->with(['rows' => function($query) {
                $query->get(['id', 'ingredient_id', 'unit_id', 'value']);
            }, 'directions' => function($query) {
                $query->get(['id', 'description']);
            }, 'rows.ingredient.units'])
            ->findOrFail($id, [
                'id', 'name', 'description', 'image'
            ]);

        return response()
            ->json([
                'form' => $form
            ]);
    }

    public function update($id, Request $request)
    {
         ddd($request->all());
        $this->validate($request, [
            'name' => 'required|max:255',
            'description' => 'max:3000',
            'image' => 'image',

//            'ingredients' => 'required|array|min:1',
//            'ingredients.*.id' => 'integer|exists:recipe_ingredients',
//            'ingredients.*.name' => 'required|max:255',
//            'ingredients.*.qty' => 'required|max:255',

            'row.ingredient_id' => 'integer|exists:ingredient',
            'row.delta' => 'required|integer|min:1',
            'row.unit_id' => 'integer|exists:unit',
            'row.value' => 'required|numeric',

            'directions' => 'required|array|min:1',
            'directions.*.id' => 'integer|exists:recipe_directions',
            'directions.*.description' => 'required|max:3000'
        ]);

        $recipe = $request->user()->recipes()
            ->findOrFail($id);

        $ingredients = [];
        $ingredientsUpdated = [];

        foreach($request->ingredients as $ingredient) {
            if(isset($ingredient['id'])) {
                RecipeIngredient::where('recipe_id', $recipe->id)
                    ->where('id', $ingredient['id'])
                    ->update($ingredient);

                $ingredientsUpdated[] = $ingredient['id'];
            } else {
                $ingredients[] = new RecipeIngredient($ingredient);
            }
        }

        $directions = [];
        $directionsUpdated = [];

        foreach($request->directions as $direction) {
            if(isset($direction['id'])) {
                RecipeDirection::where('recipe_id', $recipe->id)
                    ->where('id', $direction['id'])
                    ->update($direction);

                $directionsUpdated[] = $direction['id'];
            } else {
                $directions[] = new RecipeDirection($direction);
            }

        }

        $recipe->name = $request->name;
        $recipe->description = $request->description;

        // upload image
        if ($request->hasfile('image') && $request->file('image')->isValid()) {
            $filename = $this->getFileName($request->image);
            $request->image->move(base_path('/public/images'), $filename);

            // remove old image
            File::delete(base_path('/public/images/'.$recipe->image));
            $recipe->image = $filename;
        }

        $recipe->save();

        RecipeIngredient::whereNotIn('id', $ingredientsUpdated)
            ->where('recipe_id', $recipe->id)
            ->delete();

        RecipeDirection::whereNotIn('id', $directionsUpdated)
            ->where('recipe_id', $recipe->id)
            ->delete();

        if(count($ingredients)) {
            $recipe->ingredients()->saveMany($ingredients);
        }

        if(count($directions)) {
            $recipe->directions()->saveMany($directions);
        }

        return response()
            ->json([
                'saved' => true,
                'id' => $recipe->id,
                'message' => 'You have successfully updated recipe!'
            ]);
    }

    public function destroy($id, Request $request)
    {
        $recipe = $request->user()->recipes()
            ->findOrFail($id);

        RecipeIngredient::where('recipe_id', $recipe->id)
            ->delete();

        RecipeDirection::where('recipe_id', $recipe->id)
            ->delete();

        // remove image
        File::delete(base_path('/public/images/'.$recipe->image));

        $recipe->delete();

        return response()
            ->json([
                'deleted' => true
            ]);
    }
}
