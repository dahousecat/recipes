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
        AttributeType::truncate();

        $attribute_types = [
            'energy' =>
                [
                    'name' => 'Energy',
                    'unit' => 'cal',
                    'ndb_nutrient_id' => 208,
                ],
            'protein' =>
                [
                    'name' => 'Protein',
                    'unit' => 'g',
                    'ndb_nutrient_id' => 203,
                ],
            'total_fat' =>
                [
                    'name' => 'Total fat',
                    'unit' => 'g',
                    'ndb_nutrient_id' => 204,
                ],
            'saturated_fat' =>
                [
                    'name' => 'Saturated fat',
                    'unit' => 'g',
                    'ndb_nutrient_id' => 606,
                ],
            'carbohydrate' =>
                [
                    'name' => 'Carbohydrate',
                    'unit' => 'g',
                    'ndb_nutrient_id' => 205,
                ],
            'sugar' =>
                [
                    'name' => 'Sugar',
                    'unit' => 'g',
                    'ndb_nutrient_id' => 269,
                ],
            'fibre' =>
                [
                    'name' => 'Fibre',
                    'unit' => 'g',
                    'ndb_nutrient_id' => 291,
                ],
            'sodium' =>
                [
                    'name' => 'Sodium',
                    'unit' => 'mg',
                    'ndb_nutrient_id' => 307,
                ],
        ];

        foreach($attribute_types as $safe_name => $details) {
            DB::table('attribute_types')->insert([
                'name' => $details['name'],
                'safe_name' => $safe_name,
                'unit' => $details['unit'],
                'ndb_nutrient_id' => $details['ndb_nutrient_id'],
            ]);
        }
    }
}
