<?php

use Illuminate\Database\Seeder;
use App\Models\Ingredient;
use Faker\Factory;
use App\Models\Unit;

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

//        Ingredient::truncate();

        $ingredients = [
            [
                'name' => 'Melon',
                'unit_types' => ['quantity', 'weight'],
                'default_unit' => 'quantity',
            ],
            [
                'name' => 'Carrot',
                'unit_types' => ['quantity', 'weight'],
                'default_unit' => 'quantity',
            ],
            [
                'name' => 'Potato',
                'unit_types' => ['quantity', 'weight'],
                'default_unit' => 'quantity',
            ],
            [
                'name' => 'Orange',
                'unit_types' => ['quantity', 'weight'],
                'default_unit' => 'quantity',
            ],
            [
                'name' => 'Banana',
                'unit_types' => ['quantity', 'weight'],
                'default_unit' => 'quantity',
            ],
            [
                'name' => 'Milk',
                'unit_types' => ['volume'],
                'default_unit' => 'pint',
            ],
            [
                'name' => 'Coconut Water',
                'unit_types' => ['volume'],
                'default_unit' => 'litre',
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

        }
    }
}
