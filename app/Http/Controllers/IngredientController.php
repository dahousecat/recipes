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

class IngredientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')
            ->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ingredients = Ingredient::all();

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
        $ingredients = Ingredient::where('name', 'like', "%$text%")->get();

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
            $type['safe_name'] = str_replace(' ', '_', $type['name']);
            $form['nutrients'][$type['safe_name']] = [
                'id' => null,
                'value' => '',
                'type_id' => $type['id'],
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

//        $this->validate($request, [
//            'name' => 'required|max:255',
//            'image' => 'image',
//            'units' => 'array',
//            'units.*.id' => 'required|numeric|min:1',
//            'attributes' => 'array',
//            'attributes.*.unit_id' => 'required|numeric|min:1',
//            'attributes.*.value' => 'required|numeric',
//            'attributes.*.attribute_type_id' => 'required|numeric|min:1',
//        ]);
//
//        $unit_ids = [];
//        if(!empty($request->units)) {
//            foreach($request->units as $unit) {
//                $unit_ids[] = $unit['id'];
//            }
//        }
//
//        $attributes = [];
//        if(!empty($request->nutrients)) {
//            foreach($request->nutrients as $attribute) {
//                $attribute['attribute_type_id'] = $attribute['type_id'];
//                $attributes[] = new Attribute($attribute);
//            }
//        }
//
//        $ingredient = new Ingredient($request->only(
//            'name',
//            'weight_one',
//            'weight_one_cup',
//            'weight_one_cm',
//            'default_unit_id'
//        ));
//
//        $request->user()->ingredients()
//            ->save($ingredient);
//
//        $ingredient->units()->sync($unit_ids);
//        $ingredient->attributes()->saveMany($attributes);
//
//        return response()
//            ->json([
//                'saved' => true,
//                'id' => $ingredient->id,
//                'message' => 'You have successfully created an ingredient!'
//            ]);

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

    private function keyArray($inArray, $pathToKey) {
      $outArray = [];
      $pathToKey = is_array($pathToKey) ? $pathToKey : [$pathToKey];
      foreach($inArray as $value) {
        $ref = $value;
        foreach($pathToKey as $pathElement) {
          if(!isset($ref[$pathElement])) {
            print_r(debug_backtrace());
            trigger_error('Key ' . $pathElement . ' is not set in this array: ' . print_r($ref, true));
          } else {
            $ref = $ref[$pathElement];
          }
        }
        $outArray[$ref] = $value;
      }
      return $outArray;
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
        $this->validate($request, $this->validationRules());

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

                $attribute = NULL;

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

    private function validationRules() {
        return [
            'name' => 'required|max:255',
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

    public function test() {
        die('foobar');
    }

}
