<?php

use Illuminate\Database\Seeder;
use App\Models\AttributeType;

class AttributeTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        AttributeType::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $attribute_types = [

            // Other
            'energy' =>
                [
                    'name' => 'Energy',
                    'unit' => 'cal',
                    'ndb_nutrient_id' => 208,
                    'category' => 'other',
                ],

            // Macronutrients
            'carbohydrate' =>
                [
                    'name' => 'Carbohydrate',
                    'unit' => 'g',
                    'ndb_nutrient_id' => 205,
                    'category' => 'macronutrients',
                ],
            'sugar' =>
                [
                    'name' => 'Sugar',
                    'unit' => 'g',
                    'ndb_nutrient_id' => 269,
                    'category' => 'macronutrients',
                ],
            'fibre' =>
                [
                    'name' => 'Fibre',
                    'unit' => 'g',
                    'ndb_nutrient_id' => 291,
                    'category' => 'macronutrients',
                ],
            'protein' =>
                [
                    'name' => 'Protein',
                    'unit' => 'g',
                    'ndb_nutrient_id' => 203,
                    'category' => 'macronutrients',
                ],
            'total_fat' =>
                [
                    'name' => 'Total fat',
                    'unit' => 'g',
                    'ndb_nutrient_id' => 204,
                    'category' => 'macronutrients',
                ],
            'saturated_fat' =>
                [
                    'name' => 'Saturated fat',
                    'unit' => 'g',
                    'ndb_nutrient_id' => 606,
                    'category' => 'macronutrients',
                ],
            'trans_fat' =>
                [
                    'name' => 'Trans fat',
                    'unit' => 'g',
                    'ndb_nutrient_id' => 605,
                    'category' => 'macronutrients',
                ],
            'cholesterol' =>
                [
                    'name' => 'Cholesterol',
                    'unit' => 'mg',
                    'ndb_nutrient_id' => 601,
                    'category' => 'macronutrients',
                ],


            // Vitamins
            'vitamin_a' =>
                [
                    'name' => 'Vitamin A',
                    'unit' => 'mcg',
                    'ndb_nutrient_id' => 320,
                    'category' => 'vitamins',
                ],
            'vitamin_c' =>
                [
                    'name' => 'Vitamin C',
                    'unit' => 'mg',
                    'ndb_nutrient_id' => 401,
                    'category' => 'vitamins',
                ],
            'vitamin_d' =>
                [
                    'name' => 'Vitamin D',
                    'unit' => 'mcg',
                    'ndb_nutrient_id' => 328,
                    'category' => 'vitamins',
                ],
            'vitamin_b6' =>
                [
                    'name' => 'Vitamin B6',
                    'unit' => 'mg',
                    'ndb_nutrient_id' => 415,
                    'category' => 'vitamins',
                ],
            'vitamin_e' =>
                [
                    'name' => 'Vitamin E',
                    'unit' => 'mg',
                    'ndb_nutrient_id' => 323,
                    'category' => 'vitamins',
                ],
            'vitamin_k' =>
                [
                    'name' => 'Vitamin K',
                    'unit' => 'mcg',
                    'ndb_nutrient_id' => 430,
                    'category' => 'vitamins',
                ],
            'thiamin' =>
                [
                    'name' => 'Thiamin',
                    'unit' => 'mg',
                    'ndb_nutrient_id' => 404,
                    'category' => 'vitamins',
                ],
            'vitamin_b12' =>
                [
                    'name' => 'Vitamin B12',
                    'unit' => 'mcg',
                    'ndb_nutrient_id' => 418,
                    'category' => 'vitamins',
                ],
            'riboflavin' =>
                [
                    'name' => 'Riboflavin',
                    'unit' => 'mg',
                    'ndb_nutrient_id' => 405,
                    'category' => 'vitamins',
                ],
            'folate' =>
                [
                    'name' => 'Folate',
                    'unit' => 'mcg',
                    'ndb_nutrient_id' => 435,
                    'category' => 'vitamins',
                ],
            'niacin' =>
                [
                    'name' => 'Niacin',
                    'unit' => 'mg',
                    'ndb_nutrient_id' => 406,
                    'category' => 'vitamins',
                ],

            // Minerals
            'calcium' =>
                [
                    'name' => 'Calcium',
                    'unit' => 'mg',
                    'ndb_nutrient_id' => 301,
                    'category' => 'minerals',
                ],
            'iron' =>
                [
                    'name' => 'Iron',
                    'unit' => 'mg',
                    'ndb_nutrient_id' => 303,
                    'category' => 'minerals',
                ],
            'magnesium' =>
                [
                    'name' => 'Magnesium',
                    'unit' => 'mg',
                    'ndb_nutrient_id' => 304,
                    'category' => 'minerals',
                ],
            'phosphorus' =>
                [
                    'name' => 'Phosphorus',
                    'unit' => 'mg',
                    'ndb_nutrient_id' => 305,
                    'category' => 'minerals',
                ],
            'potassium' =>
                [
                    'name' => 'Potassium',
                    'unit' => 'mg',
                    'ndb_nutrient_id' => 306,
                    'category' => 'minerals',
                ],
            'zinc' =>
                [
                    'name' => 'Zinc',
                    'unit' => 'mg',
                    'ndb_nutrient_id' => 309,
                    'category' => 'minerals',
                ],
            'sodium' =>
                [
                    'name' => 'Sodium',
                    'unit' => 'mg',
                    'ndb_nutrient_id' => 307,
                    'category' => 'minerals',
                ],


        ];

        foreach($attribute_types as $safe_name => $details) {
            DB::table('attribute_types')->insert([
                'name' => $details['name'],
                'safe_name' => $safe_name,
                'unit' => $details['unit'],
                'ndb_nutrient_id' => $details['ndb_nutrient_id'],
                'category' => $details['category'],
            ]);
        }
    }
}
