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
        $attrs = [
            'name' => $ingredient->name,
            'description' => $ingredient->description,
            'image' => $ingredient->image,
            'weight' => $ingredient->weight,
            'default_unit_id' => $ingredient->default_unit_id,
        ];
        return $attrs;
    }

    public function getRelationships($ingredient, $isPrimary, array $includeList)
    {
        /** @var Ingredient $ingredient */
        return [
            'units' => [
                self::DATA => $ingredient->units,
            ],
            'attributes' => [
                self::DATA => $ingredient->attributes,
            ],
        ];
    }
}
