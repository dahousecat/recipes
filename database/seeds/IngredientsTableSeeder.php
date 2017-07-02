<?php

use Illuminate\Database\Seeder;
use App\Models\Ingredient;
use Faker\Factory;
use App\Models\Unit;
use App\Models\Attribute;
Use App\Models\AttributeType;

class IngredientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();

        Ingredient::truncate();

        $ingredients = [
            [
                'name' => 'Melon',
                'weight' => 2000,
                'unit_types' => ['quantity', 'weight'],
                'default_unit' => 'quantity',
                'attributes' => [
                    [
                        'attribute_type' => 'energy',
                        'value' => 36,
                    ],
                    [
                        'attribute_type' => 'sugar',
                        'value' => 0.8,
                    ],
                    [
                        'attribute_type' => 'protein',
                        'value' => 0.5,
                    ],
                ],
            ],
            [
                'name' => 'Carrot',
                'weight' => 60,
                'unit_types' => ['quantity', 'weight'],
                'default_unit' => 'quantity',
                'attributes' => [
                    [
                        'attribute_type' => 'energy',
                        'value' => 41,
                    ],
                    [
                        'attribute_type' => 'sugar',
                        'value' => 4.7,
                    ],
                    [
                        'attribute_type' => 'protein',
                        'value' => 0.9,
                    ],
                ],
            ],
            [
                'name' => 'Coconut Water',
                'unit_types' => ['volume'],
                'default_unit' => 'litre',
                'attributes' => [
                    [
                        'attribute_type' => 'energy',
                        'value' => 19,
                    ],
                    [
                        'attribute_type' => 'sugar',
                        'value' => 5,
                    ],
                    [
                        'attribute_type' => 'protein',
                        'value' => 0.7,
                    ],
                ],
            ],
            [
                'name' => 'Potato',
                'unit_types' => ['quantity', 'weight'],
                'default_unit' => 'quantity',
                'weight' => 150,
            ],
            [
                'name' => 'Orange',
                'unit_types' => ['quantity', 'weight'],
                'default_unit' => 'quantity',
                'weight' => 200,
            ],
            [
                'name' => 'Banana',
                'unit_types' => ['quantity', 'weight'],
                'default_unit' => 'quantity',
                'weight' => 120,
            ],
            [
                'name' => 'Milk',
                'unit_types' => ['volume'],
                'default_unit' => 'pint',
            ],
            [
                'name' => 'Peach',
                'unit_types' => ['quantity', 'weight'],
                'default_unit' => 'quantity',
            ],
            [
                'name' => 'Turnip',
                'unit_types' => ['quantity', 'weight'],
                'default_unit' => 'quantity',
            ],
            [
                'name' => 'Flour',
                'unit_types' => ['volume', 'weight'],
                'default_unit' => 'kilogram',
            ],
        ];

        $units_by_type = [];

        foreach($ingredients as $i => $data) {
            $default_unit = Unit::loadByName($data['default_unit']);
            $ingredient = Ingredient::create([
                'user_id' => $i,
                'name' => $data['name'],
                'weight' => isset($data['weight']) ? $data['weight'] : NULL,
                'description' => $faker->paragraph(mt_rand(10, 20)),
                'image' => 'test.png',
                'default_unit_id' => empty($default_unit) ? NULL : $default_unit->id,
            ]);

            if(!empty($data['unit_types'])) {
                foreach($data['unit_types'] as $type) {
                    if(!isset($units_by_type[$type])) {
                        $units_by_type[$type] = Unit::loadByType($type);
                    }
                    $ingredient->units()->saveMany($units_by_type[$type]);
                }
            }

            if(!empty($data['attributes'])) {
                foreach($data['attributes'] as $attribute) {

                    $attribute_type = AttributeType::loadByName($attribute['attribute_type']);

                    Attribute::create([
                        'ingredient_id' => $ingredient->id,
                        'value' => $attribute['value'],
                        'attribute_type_id' => $attribute_type->id,
                    ]);

                }
            }

        }
    }
}
