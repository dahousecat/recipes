<?php

use Illuminate\Database\Seeder;

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
            'energy' => 'kJ',
            'protein' => 'g',
            'total fat' => 'g',
            'saturated fat' => 'g',
            'carbohydrate' => 'g',
            'sugar' => 'g',
            'fibre' => 'g',
            'sodium' => 'mg',
        ];

        foreach($attribute_types as $name => $unit) {
            DB::table('attribute_types')->insert([
                'name' => $name,
                'unit' => $unit,
            ]);
        }
    }
}
