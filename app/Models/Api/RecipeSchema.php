<?php

namespace App\Models\Api;

use Neomerx\JsonApi\Schema\SchemaProvider;

class RecipeSchema extends SchemaProvider
{
    protected $resourceType = 'recipe';

    public function getId($recipe)
    {
        /** @var Recipe $recipe */
        return $recipe->id;
    }

    public function getAttributes($recipe)
    {
        /** @var Recipe $recipe */
        return [
            'name' => $recipe->name,
            'description' => $recipe->description,
            'image' => $recipe->image,
        ];
    }

    public function getRelationships($recipe, $isPrimary, array $includeList)
    {
        /** @var Recipe $recipe */
        return [
            'user' => [
                self::DATA => $recipe->user
            ],
        ];
    }
}
