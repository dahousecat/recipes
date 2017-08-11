<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeType;
use App\Models\Ingredient;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\In;
Use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Encoder\Parameters\EncodingParameters;
use Illuminate\Validation\Rule;

class IngredientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')
            ->except(['index', 'show', 'search']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ingredients = Ingredient::select('id', 'name', 'default_unit_id')->get();

        return response()
            ->json([
                'ingredients' => $ingredients
            ]);

        // Encode the model data for json:api consumption
        $encoder = Encoder::instance($this->modelSchemaMappings, $this->encoderOptions);
        $encodedData = $encoder->encodeData($ingredients);

        return response($encodedData)
            ->header('Content-Type', 'application/json');
    }

    public function search($text)
    {
        $ingredients = Ingredient::where('name', 'like', "%$text%")->select('id', 'name', 'default_unit_id')->get();

        return response()
            ->json([
                'ingredients' => $ingredients
            ]);
    }

    public function attributes($id) {
        $ingredient = Ingredient::with('attributes.AttributeType')->find($id);

        $attributes = $ingredient->attributes->toArray();
        $data = [];

        foreach($attributes as $attribute) {
            $key = str_replace(' ', '_', $attribute['attribute_type']['name']);
            $data[$key] = $attribute;
        }

        return response()
            ->json([
                'attributes' => $data,
            ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $form = Ingredient::form();
        $unitsArr = Unit::all()->toArray();
        $units = [];
        foreach($unitsArr as $unit) {
            $units[$unit['name']] = $unit;
        }

        $attributeTypes = AttributeType::all()->toArray();
        foreach($attributeTypes as &$type) {
            $form['nutrients'][$type['safe_name']] = [
                'id' => null,
                'value' => '',
                'attribute_type_id' => $type['id'],
                'type_name' => $type['name'],
            ];
        }

        return response()
            ->json([
                'form' => $form,
                'units' => $units,
                'attributeTypes' => $attributeTypes,
            ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validationRules());

        $ingredient = new Ingredient($request->only(
            'name',
            'weight_one',
            'weight_one_cup',
            'weight_one_cm',
            'default_unit_id'
        ));
        $ingredient->user_id = $request->user()->id;
        $ingredient->save();

        return $this->update($ingredient->id, $request);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Ingredient  $ingredient
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $ingredient = Ingredient::with('units', 'attributes.attributeType')->findOrFail($id)->toArray();

        $ingredient['nutrients'] = $this->keyArray($ingredient['attributes'], ['attribute_type', 'safe_name']);
        unset($ingredient['attributes']);

        $ingredient['units'] = $this->keyArray($ingredient['units'], 'name');

        // In the DB we store the weight of one cup - but the weight on one ml will be more useful so convert now
        if(!empty($ingredient['weight_one_cup'])) {
            $ingredient['weight_one_ml'] = $ingredient['weight_one_cup'] / self::ML_IN_CUP;
        }

        return response()
            ->json([
                'ingredient' => $ingredient,
            ]);
    }

    public function edit($id, Request $request)
    {

        $form = Ingredient::with('units', 'attributes.attributeType')->find($id)->toArray();
        $form['units'] = $this->keyArray($form['units'], 'name');

        $form['nutrients'] = $this->keyArray($form['attributes'], ['attribute_type', 'safe_name']);
        unset($form['attributes']);

        $attributeTypes = AttributeType::all()->toArray();
        $units = $this->keyArray(Unit::all()->toArray(), 'name');

        // Add default values for attributes that don't exist
        // TODO: Find out if this is really necessary... seems surplus
        foreach($attributeTypes as $type) {
          if(!isset($form['nutrients'][$type['safe_name']])) {
            $form['nutrients'][$type['safe_name']] = [
              'attribute_type' => $type,
              'attribute_type_id' => $type['id'],
              'id' => null,
              'ingredient_id' => $id,
              'value' => '',
            ];
          }
        }

        return response()
            ->json([
                'form' => $form,
                'units' => $units,
                'attributeTypes' => $attributeTypes,
            ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $this->validate($request, $this->validationRules($id));

//        Validator::make($data, [
//            'email' => [
//                'required',
//                Rule::unique('users')->ignore($user->id),
//            ],
//        ]);

        $response = [];

        $ingredient = Ingredient::find($id);

        $ingredient->name            = $request->name;
        $ingredient->description     = $request->description;
        $ingredient->weight_one      = $request->weight_one;
        $ingredient->weight_one_cup  = $request->weight_one_cup;
        $ingredient->weight_one_cm   = $request->weight_one_cm;
        $ingredient->default_unit_id = $request->default_unit_id;

        $ingredient->save();

        $ingredient->units()->sync($request->units);

        // Get a list of all existing attribute ids
        $idsToDelete = array_fill_keys($ingredient->attributeIds(), null);

        if(!empty($request->nutrients)) {

            foreach($request->nutrients as $attributeData) {

                // Don't create an attribute with an empty value
                if(empty($attributeData['value'])) {
                    continue;
                }

                $attribute = null;

                // Try and load an existing attribute
                if(!empty($attributeData['id'])) {
                    $attribute = Attribute::find($attributeData['id']);
                    $response['method'] = 'loaded existing attribute';
                    // Once we loaded an attribute remove it from the list of all ids
                    unset($idsToDelete[$attribute->id]);
                }

                // Create a new attribute if necessary
                if(empty($attribute)) {
                    $attribute = new Attribute;
                    $response['method'] = 'created new attribute';
                }

                $attribute->value = $attributeData['value'];
                $attribute->ingredient_id = $ingredient->id;

                $attributeType = AttributeType::find($attributeData['attribute_type_id']);
                if(!empty($attributeType)) {
                    $attribute->attribute_type_id = $attributeType->id;
                } else {
                    $response['error'] = 'Invalid attribute type id';
                }

                $response['attributes'][] = $attribute->toJson();

                $attribute->save();

            }

        }

        // If there are any attribute ids left in here then they have been deleted
        if(!empty($idsToDelete)) {
            $idsToDelete = array_keys($idsToDelete);
            Attribute::deleteMany($idsToDelete);
            $response['deleted_attribute_ids'] = $idsToDelete;
        }

        if(empty($response['error'])) {
            $response['saved'] = true;
            $response['id'] = $ingredient->id;
            $response['message'] = 'Saved changes to ' . $ingredient->name;
        }

        return response()
            ->json($response);
    }

    private function validationRules($id = null) {
        return [
            'name' => 'required|max:255|unique:ingredients,name' . ($id ? ',' . $id : ''),
            'weight_one' => 'required_if_quantity_unit|present|positive',
            'weight_one_cup' => 'required_if_volume_unit|present|positive',
            'weight_one_cm' => 'required_if_length_unit|present|positive',
            'units' => 'array|required',
            'attributes' => 'array',
            'attributes.*.unit_id' => 'required|numeric|min:1',
            'attributes.*.value' => 'required|numeric',
            'attributes.*.attribute_type_id' => 'required|numeric|min:1',
            'default_unit_id' => 'numeric|min:1|required',
        ];
    }

    /**
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id, Request $request)
    {
        $ingredient = Ingredient::findOrFail($id);
        $ingredient->delete();

        return response()
            ->json([
                'deleted' => true
            ]);
    }

}
