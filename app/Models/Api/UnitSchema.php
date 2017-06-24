<?php

namespace App\Models\Api;

use Neomerx\JsonApi\Schema\SchemaProvider;

class UnitSchema extends SchemaProvider
{
    protected $resourceType = 'unit';

    public function getId($unit)
    {
        /** @var Unit $unit */
        return $unit->id;
    }

    public function getAttributes($unit)
    {
        /** @var Unit $unit */
        return [
            'name' => $unit->name,
            'unitType' => $unit->type,
            'mm' => $unit->mm,
            'gram' => $unit->gram,
            'ml' => $unit->ml,
        ];
    }

    public function getRelationships($unit, $isPrimary, array $includeList)
    {
        /** @var Unit $unit */
        return [
//            'ingredient' => $unit->ingredients,
        ];
    }
}
