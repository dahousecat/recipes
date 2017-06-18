<?php

use Illuminate\Database\Seeder;
use App\Models\Ingredient;
use Faker\Factory;

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

        foreach(range(1, 10) as $i) {
            $ingredient = Ingredient::create([
                'user_id' => $i,
                'name' => $faker->sentence(2),
                'description' => $faker->paragraph(mt_rand(10, 20)),
                'image' => 'test.png'
            ]);
        }
    }
}
