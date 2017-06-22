<?php

namespace App\Models\Api;

use Neomerx\JsonApi\Schema\SchemaProvider;

class AttributeSchema extends SchemaProvider
{
    protected $resourceType = 'attribute';

    public function getId($attribute)
    {
        /** @var Attribute $attribute */
        return $attribute->id;
    }

    public function getAttributes($attribute)
    {
        /** @var Attribute $attribute */
        $attrs = [
            'value' => $attribute->value,
            'attributeType' => [
                'name' => $attribute->attributeType->name,
                'unit' => $attribute->attributeType->unit,
            ],
        ];
        return $attrs;
    }

    public function getRelationships($attribute, $isPrimary, array $includeList)
    {
        return [
            'unit' => [
                self::DATA => $attribute->unit,
            ],
            'ingredient' => [
                self::DATA => $attribute->ingredient,
            ],
        ];
    }
}
