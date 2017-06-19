<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
Use Neomerx\JsonApi\Encoder\Encoder;

class UnitController extends JsonApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $units = Unit::all();

        // Encode the model data for json:api consumption
        $encoder = Encoder::instance($this->modelSchemaMappings, $this->encoderOptions);
        $encodedData = $encoder->encodeData($units);
        return response($encodedData)
            ->header('Content-Type', 'application/json');
    }

}
