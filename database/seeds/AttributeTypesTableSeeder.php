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
                    'unit' => 'kJ',
                    'ndb_nutrient_id' => 208,
                ],
            'protein' =>
                [
                    'unit' => 'g',
                    'ndb_nutrient_id' => 203,
                ],
            'total fat' =>
                [
                    'unit' => 'g',
                    'ndb_nutrient_id' => 204,
                ],
            'saturated fat' =>
                [
                    'unit' => 'g',
                    'ndb_nutrient_id' => 606,
                ],
            'carbohydrate' =>
                [
                    'unit' => 'g',
                    'ndb_nutrient_id' => 205,
                ],
            'sugar' =>
                [
                    'unit' => 'g',
                    'ndb_nutrient_id' => 269,
                ],
            'fibre' =>
                [
                    'unit' => 'g',
                    'ndb_nutrient_id' => 291,
                ],
            'sodium' =>
                [
                    'unit' => 'mg',
                    'ndb_nutrient_id' => 307,
                ],
        ];

        foreach($attribute_types as $name => $details) {
            DB::table('attribute_types')->insert([
                'name' => $name,
                'unit' => $details['unit'],
                'ndb_nutrient_id' => $details['ndb_nutrient_id'],
            ]);
        }
    }
}
