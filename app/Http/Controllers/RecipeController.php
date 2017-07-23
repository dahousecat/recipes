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
        if(isset($_GET['sortBy'])) {

            $recipes = Recipe::recipesSortedByAttribute($_GET['sortBy']);

        } else {
            $recipes = Recipe::orderBy('created_at', 'desc')
                ->get(['id', 'name', 'image']);
        }


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

        $message = 'You have successfully created a recipe!';
        return $this->update($recipe->id, $request, $message);

    }

    private function getFileName($file)
    {
    	return str_random(32).'.'.$file->extension();
    }

    public function show($id)
    {

        $recipe = Recipe::with(
            'user',
            'rows.ingredient.attributes.attributeType',
            'rows.ingredient.units',
            'directions'
        )->findOrFail($id)->toArray();

        $this->prepareRows($recipe['rows']);

        $units = Unit::all()->toArray();

        return response()
            ->json([
                'recipe' => $recipe,
                'units' => $units,
            ]);
    }

    private function prepareRows(&$rows) {
        foreach($rows as &$row) {
            $row['recalculate'] = false;
            $ingredient =& $row['ingredient'];
            $ingredient['units'] = $this->keyArray($ingredient['units'], 'name');
            $ingredient['nutrients'] = $this->keyArray($ingredient['attributes'], ['attribute_type', 'safe_name']);

            // In the DB we store the weight of one cup - but the weight on one ml will be more useful so convert now
            if(!empty($ingredient['weight_one_cup'])) {
                $ingredient['weight_one_ml'] = $ingredient['weight_one_cup'] / self::ML_IN_CUP;
            }
        }
    }

    public function edit($id, Request $request)
    {
        $form = $request->user()->recipes()->with(
            'rows.ingredient.attributes.attributeType',
            'rows.ingredient.units',
            'directions'
        )->findOrFail($id)->toArray();

        $this->prepareRows($form['rows']);

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

    public function update($id, Request $request, $message = 'You have successfully updated a recipe!')
    {
        $this->validate($request, $this->validationRules());

        $recipe = Recipe::find($id);

        $recipe->name = $request->name;
        $recipe->description = $request->description;

        $response = [];

        // Get a list of all existing row ids
        $rowIdsToDelete = array_fill_keys($recipe->rowIds(), null);

        if(!empty($request->rows)) {
            foreach($request->rows as $rowData) {
                $row = null;
                if(isset($rowData['id'])) {
                    $row = Row::find($rowData['id']);

                    // Once we loaded a row remove it from the list of all ids
                    unset($rowIdsToDelete[$row->id]);
                } else {
                    $row = new Row();
                }

                $row->recipe_id     = $recipe->id;
                $row->ingredient_id = $rowData['ingredient_id'];
                $row->delta         = $rowData['delta'];
                $row->unit_id       = $rowData['unit_id'];
                $row->value         = $rowData['value'];
                $row->weight        = $rowData['weight'];

                $row->save();
            }
        }

        // If there are any row ids left in here then they have been deleted
        if(!empty($rowIdsToDelete)) {
            $rowIdsToDelete = array_keys($rowIdsToDelete);
            Row::deleteMany($rowIdsToDelete);
            $response['deleted_attribute_ids'] = $rowIdsToDelete;
        }

        // Get a list of all existing direction ids
        $directionIdsToDelete = array_fill_keys($recipe->directionIds(), null);

        if(!empty($request->directions)) {
            foreach($request->directions as $directionData) {
                if(empty($directionData['description'])) {
                    continue;
                }

                if(!empty($direction['id'])) {
                    $direction = RecipeDirection::find($direction['id']);
                    unset($directionIdsToDelete[$direction['id']]);
                } else {
                    $direction = new RecipeDirection();
                }

                $direction->recipe_id = $recipe->id;
                $direction->description = $directionData['description'];
                $direction->save();

            }
        }

        if($request->hasFile('image') && $request->file('image')->isValid()) {
            $filename = $this->getFileName($request->image);
            $request->image->move(base_path('public/images'), $filename);
            $recipe->image = $filename;
        }

        $request->user()->recipes()->save($recipe);

        return response()
            ->json([
                'saved' => true,
                'id' => $recipe->id,
                'message' => $message
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

        Row::where('recipe_id', $recipe->id)
            ->delete();

        RecipeDirection::where('recipe_id', $recipe->id)
            ->delete();

        // remove image
        File::delete(base_path('/public/images/' . $recipe->image));

        $recipe->delete();

        return response()
            ->json([
                'deleted' => true
            ]);
    }
}
