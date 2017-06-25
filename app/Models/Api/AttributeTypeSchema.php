<?php

namespace App\Models\Api;

use Neomerx\JsonApi\Schema\SchemaProvider;

class AttributeTypeSchema extends SchemaProvider
{
    protected $resourceType = 'attributeType';

    public function getId($attributeType)
    {
        /** @var Attribute $attributeType */
        return $attributeType->id;
    }

    public function getAttributes($attributeType)
    {
        /** @var Attribute $attributeType */
        $attrs = [
            'name' => $attributeType->name,
            'safe_name' => str_replace(' ', '_', $attributeType->name),
            'unit' => $attributeType->unit,
        ];
        return $attrs;
    }

    public function getRelationships($attributeType, $isPrimary, array $includeList)
    {
        return [
        ];
    }
}
