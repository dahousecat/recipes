<?php

use Illuminate\Database\Seeder;
use App\Models\Attribute;

class AttributesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Attribute::truncate();

        $attributes = [
            'Energy' => 'kJ',
            'Protein' => 'g',
            'Total fat' => 'g',
            'Saturated fat' => 'g',
            'Carbohydrate' => 'g',
            'Sugars' => 'g',
            'Fibre' => 'g',
            'Sodium' => 'mg',
        ];

        foreach($attributes as $name => $unit) {
            DB::table('attributes')->insert([
                'name' => $name,
                'unit' => $unit,
            ]);
        }
    }
}
