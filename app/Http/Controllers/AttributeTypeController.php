<?php

namespace App\Http\Controllers;

use App\Models\AttributeType;
Use Neomerx\JsonApi\Encoder\Encoder;

class AttributeTypeController extends JsonApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $attributeTypes = AttributeType::all();

        // Encode the model data for json:api consumption
        $encoder = Encoder::instance($this->modelSchemaMappings, $this->encoderOptions);
        $encodedData = $encoder->encodeData($attributeTypes);

        return response($encodedData)
            ->header('Content-Type', 'application/json');
    }

}
