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

class IngredientController extends JsonApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ingredients = Ingredient::all();

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
        $ingredient = Ingredient::find($id);

        $encoder = Encoder::instance($this->modelSchemaMappings, $this->encoderOptions);
        $encodedData = $encoder->encodeData($ingredient->attributes);

        return response($encodedData)
            ->header('Content-Type', 'application/json');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'description' => 'required|max:3000',
            'image' => 'image',
            'weight' => 'required|numeric|min:0',
            'units' => 'array',
            'units.*.id' => 'required|numeric|min:1',
            'attributes' => 'array',
            'attributes.*.unit_id' => 'required|numeric|min:1',
            'attributes.*.value' => 'required|numeric',
            'attributes.*.attribute_type_id' => 'required|numeric|min:1',
        ]);

        die('STORE');

//        $user = $request->user();

//        'name',
//        'description',
//        'image',
//        'weight',

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Ingredient  $ingredient
     * @return \Illuminate\Http\Response
     */
//    public function show(Ingredient $ingredient)
//    {
//        //
//    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Ingredient  $ingredient
     * @return \Illuminate\Http\Response
     */
//    public function edit(Ingredient $ingredient)
//    {
//        //
//    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'description' => 'required|max:3000',
            'image' => 'image',
            'weight' => 'required|numeric|min:0',
            'units_types' => 'array',
            'units_types.*.name' => 'required|string',
            'units' => 'array',
            'units.*' => 'required|numeric|min:1',
            'ingredientAttributes' => 'array',
            'ingredientAttributes.*.id' => 'numeric|min:1',
            'ingredientAttributes.*.unit_id' => 'required|numeric|min:1',
            'ingredientAttributes.*.value' => 'required|numeric',
            'ingredientAttributes.*.attribute_type_id' => 'required|numeric|min:1',
        ]);

        $response = [];

        $ingredient = Ingredient::find($id);

        $ingredient->name = $request->name;
        $ingredient->description = $request->description;
        $ingredient->weight = $request->weight;
        $ingredient->default_unit_id = $request->default_unit_id;

        $ingredient->save();

        $ingredient->units()->sync($request->units);

        // Get a list of all existing attribute ids
        $existingAttributeIds = $ingredient->attributeIds();
        $existingAttributeIds = array_combine($existingAttributeIds, $existingAttributeIds);

        if(!empty($request->ingredientAttributes)) {

            foreach($request->ingredientAttributes as $attributeData) {

                $attribute = NULL;

                // Try and load an existing attribute
                if(!empty($attributeData['id'])) {
                    $attribute = Attribute::find($attributeData['id']);
                    $response['method'] = 'loaded existing attribute';
                    // Once we loaded an attribute remove it from the list of all ids
                    unset($existingAttributeIds[$attribute->id]);
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

                $unit = Unit::find($attributeData['unit_id']);
                if(!empty($unit)) {
//                    $attribute->unit()->save($unit);
                    $attribute->unit_id = $unit->id;
                } else {
                    $response['error'] = 'Invalid unit id';
                }

                $response['attributes'][] = $attribute->toJson();

                $attribute->save();

            }

        }

        // If there are any attribute ids left in here then they have been deleted
        if(!empty($existingAttributeIds)) {
            Attribute::whereIn('id', $existingAttributeIds)->delete();
            $response['deleted_attribute_ids'] = $existingAttributeIds;
        }

        if(empty($response['error'])) {
            $response['saved'] = true;
            $response['id'] = $ingredient->id;
            $response['message'] = 'Saved changes to ' . $ingredient->name;
        }

        return response()
            ->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ingredient  $ingredient
     * @return \Illuminate\Http\Response
     */
//    public function destroy(Ingredient $ingredient)
//    {
//        //
//    }
}
