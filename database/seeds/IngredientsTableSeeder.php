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
                        'unit' => 'gram',
                        'attribute_type' => 'energy',
                        'value' => 1.50624,
                    ],
                    [
                        'unit' => 'gram',
                        'attribute_type' => 'sugar',
                        'value' => 0.08,
                    ],
                    [
                        'unit' => 'gram',
                        'attribute_type' => 'protein',
                        'value' => 0.005,
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
                        'unit' => 'gram',
                        'attribute_type' => 'energy',
                        'value' => 1.71544,
                    ],
                    [
                        'unit' => 'gram',
                        'attribute_type' => 'sugar',
                        'value' => 0.047,
                    ],
                    [
                        'unit' => 'gram',
                        'attribute_type' => 'protein',
                        'value' => 0.009,
                    ],
                ],
            ],
            [
                'name' => 'Coconut Water',
                'unit_types' => ['volume'],
                'default_unit' => 'litre',
                'attributes' => [
                    [
                        'unit' => 'gram',
                        'attribute_type' => 'energy',
                        'value' => 0.79496,
                    ],
                    [
                        'unit' => 'gram',
                        'attribute_type' => 'sugar',
                        'value' => 0.05,
                    ],
                    [
                        'unit' => 'gram',
                        'attribute_type' => 'protein',
                        'value' => 0.007,
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

                    $unit = Unit::loadByName($attribute['unit']);
                    $attribute_type = AttributeType::loadByName($attribute['attribute_type']);

                    Attribute::create([
                        'unit_id' => $unit->id,
                        'ingredient_id' => $ingredient->id,
                        'value' => $attribute['value'],
                        'attribute_type_id' => $attribute_type->id,
                    ]);

                }
            }

        }
    }
}
