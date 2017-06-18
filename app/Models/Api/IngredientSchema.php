<?php

namespace App\Models\Api;

use Neomerx\JsonApi\Schema\SchemaProvider;

class IngredientSchema extends SchemaProvider
{
    protected $resourceType = 'ingredient';

    public function getId($ingredient)
    {
        /** @var Ingredient $ingredient */
        return $ingredient->id;
    }

    public function getAttributes($ingredient)
    {
        /** @var Ingredient $ingredient */
        return [
            'name' => $ingredient->name,
            'description' => $ingredient->description,
            'image' => $ingredient->image,
        ];
    }

    public function getRelationships($ingredient, $isPrimary, array $includeList)
    {
//        ddd($ingredient->units->all());

        /** @var Ingredient $ingredient */
        return [
//            'user' => [
//                self::DATA => $ingredient->user
//            ],
            'units' => $ingredient->units->all()
        ];
    }
}
