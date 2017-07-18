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

class RecipeController extends Controller
//class RecipeController extends JsonApiController
{
    public function __construct()
    {
    	$this->middleware('auth:api')
    		->except(['index', 'show']);

//    	parent::__construct();
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
        $ingredients = Ingredient::select('id', 'name', 'default_unit_id')
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
    	$this->validate($request, $this->validationRules());

        $recipe = new Recipe($request->only('name', 'description'));
        $request->user()->recipes()->save($recipe);

        $this->update($recipe->id, $request);

//    	$rows = [];
//        foreach($request->rows as $row) {
//            $rows[] = new Row($row);
//        }
//
//	    $directions = [];
//        if(!empty($request->directions)) {
//            foreach($request->directions as $direction) {
//                if(empty($direction->description)) {
//                    continue;
//                }
//                $directions[] = new RecipeDirection($direction);
//            }
//        }
//
//        $recipe = new Recipe($request->only('name', 'description'));
//
//    	if($request->hasFile('image') && $request->file('image')->isValid()) {
//            $filename = $this->getFileName($request->image);
//            $request->image->move(base_path('public/images'), $filename);
//            $recipe->image = $filename;
//    	}
//
//    	$request->user()->recipes()->save($recipe);
//
//    	$recipe->rows()->saveMany($rows);
//    	$recipe->directions()->saveMany($directions);
//
//    	return response()
//    	    ->json([
//    	        'saved' => true,
//    	        'id' => $recipe->id,
//                'message' => 'You have successfully created recipe!'
//    	    ]);
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
        $form = $request->user()->recipes()->with(
            'rows.ingredient.attributes.attributeType',
            'rows.ingredient.units',
//            'rows.unit',
            'directions'
        )->findOrFail($id)->toArray();

        foreach($form['rows'] as &$row) {
            $row['recalculate'] = false;
            $ingredient =& $row['ingredient'];
            $ingredient['units'] = $this->keyArray($ingredient['units'], 'name');
            $ingredient['nutrients'] = $this->keyArray($ingredient['attributes'], ['attribute_type', 'safe_name']);

            // In the DB we store the weight of one cup - but the weight on one ml will be more useful so convert now
            if(!empty($ingredient['weight_one_cup'])) {
                $ingredient['weight_one_ml'] = $ingredient['weight_one_cup'] / self::ML_IN_CUP;
            }
        }

//            ->with(['rows' => function($query) {
//                $query->get(['id', 'ingredient_id', 'unit_id', 'value']);
//            }, 'directions' => function($query) {
//                $query->get(['id', 'description']);
//            }, 'rows.ingredient.units'])
//            ->findOrFail($id, [
//                'id', 'name', 'description', 'image'
//            ]);

        $units = Unit::all()->toArray();
        $ingredients = Ingredient::select('id', 'name', 'default_unit_id')
            ->limit(20)->get();
        $attributeTypes = AttributeType::all()->toArray();

        return response()
            ->json([
                'form' => $form,
                'ingredients' => $ingredients,
                'units' => $units,
                'attributeTypes' => $attributeTypes,
            ]);

        //////




        return response()
            ->json([
                'form' => $form
            ]);
    }

    public function update($id, Request $request)
    {
        $this->validate($request, $this->validationRules());

        $recipe = Recipe::find($id);

        // Get a list of all existing row ids
        $idsToDelete = array_fill_keys($recipe->rowIds(), null);

        $rows = [];
        foreach($request->rows as $rowData) {

            $row = null;

            if(isset($row['id'])) {
                $row = Row::find($row['id']);

                // Once we loaded a row remove it from the list of all ids
                unset($idsToDelete[$row->id]);
            }
            $row = new Row($row);
        }

        $directions = [];
        if(!empty($request->directions)) {
            foreach($request->directions as $direction) {
                if(empty($direction->description)) {
                    continue;
                }
                $directions[] = new RecipeDirection($direction);
            }
        }

        if($request->hasFile('image') && $request->file('image')->isValid()) {
            $filename = $this->getFileName($request->image);
            $request->image->move(base_path('public/images'), $filename);
            $recipe->image = $filename;
        }

        $request->user()->recipes()->save($recipe);

        $recipe->rows()->saveMany($rows);
        $recipe->directions()->saveMany($directions);

        return response()
            ->json([
                'saved' => true,
                'id' => $recipe->id,
                'message' => 'You have successfully created recipe!'
            ]);

        ////////////////////

        $recipe = $request->user()->recipes()
            ->findOrFail($id);

        $ingredients = [];
        $ingredientsUpdated = [];

        if(!empty($request->ingredients)) {
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
        }

        $directions = [];
        $directionsUpdated = [];

        if(!empty($request->directions)) {
            foreach($request->directions as $direction) {
                if(isset($direction['id'])) {
                    RecipeDirection::where('recipe_id', $recipe->id)
                        ->where('id', $direction['id'])
                        ->update($direction);

                    $directionsUpdated[] = $direction['id'];
                } else {
                    echo 'new direction<br>';
                    $directions[] = new RecipeDirection($direction);
                }

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

    private function validationRules($id = null) {
        return [
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
        ];
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
