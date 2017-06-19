<?php

use Illuminate\Database\Seeder;

class AttributeTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AttributeTypes::truncate();

        $attribute_types = [
            'Energy' => 'kJ',
            'Protein' => 'g',
            'Total fat' => 'g',
            'Saturated fat' => 'g',
            'Carbohydrate' => 'g',
            'Sugars' => 'g',
            'Fibre' => 'g',
            'Sodium' => 'mg',
        ];

        foreach($attribute_types as $name => $unit) {
            DB::table('attribute_types')->insert([
                'name' => $name,
                'unit' => $unit,
            ]);
        }
    }
}
