<?php

namespace App\Http\Controllers;

//use App\Models\Api\IngredientSchema;
//use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\Api\RecipeSchema;
use App\Models\User;
use App\Models\Ingredient;
use App\Models\Api\IngredientSchema;
use App\Models\Api\UserSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Encoder\EncoderOptions;


class JsonApiController extends Controller
{
    protected $modelSchemaMappings = [
        Recipe::class => RecipeSchema::class,
        User::class => UserSchema::class,
        Ingredient::class => IngredientSchema::class,
    ];

    protected $encoderOptions;
    protected $defaultEncoderOptions = [
        'options' => JSON_PRETTY_PRINT,
        'urlPrefix' => '/api',
    ];

    public function __construct()
    {
        $this->encoderOptions = new EncoderOptions(
            $this->defaultEncoderOptions['options'],
            $this->defaultEncoderOptions['urlPrefix']
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $model = Model::findOrFail($id);

        $encoder = Encoder::instance($this->modelSchemaMappings, $this->encoderOptions);
        $encodedData = $encoder->encodeData($model);

        return response($encoder->encodeData($encodedData))
            ->header('Content-Type', 'application/json');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $id
     * @param Request $request
     */
    public function edit($id, Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * @param  int  $id
     */
    public function update($id, Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        //
    }
}
