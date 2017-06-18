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
                'name' =>'Melon',
                'units' => ['quantity', 'weight'],
            ],
            [
                'name' =>'Carrot',
                'units' => ['quantity', 'weight'],
            ],
            [
                'name' =>'Potato',
                'units' => ['quantity', 'weight'],
            ],
            [
                'name' =>'Orange',
                'units' => ['quantity', 'weight'],
            ],
            [
                'name' =>'Banana',
                'units' => ['quantity', 'weight'],
            ],
            [
                'name' =>'Milk',
                'units' => ['volume'],
            ],
            [
                'name' =>'Coconut Water',
                'units' => ['volume'],
            ],
            [
                'name' =>'Peach',
                'units' => ['quantity', 'weight'],
            ],
            [
                'name' =>'Turnip',
                'units' => ['quantity', 'weight'],
            ],
        ];

        $units_by_type = [];
//        $units = Unit::getAllKeyed();
//        $unit_types = Unit::getTypes(); // volume, weight, length, quantity

        foreach($ingredients as $i => $data) {
            $ingredient = Ingredient::create([
                'user_id' => $i,
                'name' => $data['name'],
                'description' => $faker->paragraph(mt_rand(10, 20)),
                'image' => 'test.png'
            ]);

            if(!empty($data['units'])) {
                foreach($data['units'] as $type) {
                    if(!isset($units_by_type[$type])) {
                        $units_by_type[$type] = Unit::loadByType($type);
                    }
                    $ingredient->units()->saveMany($units_by_type[$type]);
                }
            }

        }
    }
}
