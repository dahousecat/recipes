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
            'attribute_type_id' => $attribute->attributeType->id,
            'type_name' => $attribute->attributeType->name,
            'type_safe_name' => str_replace(' ', '_', $attribute->attributeType->name),
        ];
        return $attrs;
    }

    public function getRelationships($attribute, $isPrimary, array $includeList)
    {
        return [
            'ingredient' => [
                self::DATA => $attribute->ingredient,
            ],
        ];
    }
}
