<?php

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Unit::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $units = [
            'millilitre' => [
                'type' => 'volume',
                'ml' => 1,
                'abbr' => 'ml',
            ],
            'litre' => [
                'type' => 'volume',
                'ml' => 1000,
                'abbr' => 'l',
            ],
            'cup' => [
                'type' => 'volume',
                'ml' => 240,
                'abbr' => 'cup',
            ],
            'fluid_ounce' => [
                'type' => 'volume',
                'ml' => 29.57353,
                'abbr' => 'fl oz',
            ],
            'pint' => [
                'type' => 'volume',
                'ml' => 473.176,
                'abbr' => 'pint',
            ],
            'teaspoon' => [
                'type' => 'volume',
                'ml' => 5,
                'abbr' => 'tsp',
            ],
            'table_spoon' => [
                'type' => 'volume',
                'ml' => 17.75,
                'abbr' => 'tbsp',
            ],
            'pinch' => [
                'type' => 'volume',
                'ml' => 0.3,
                'abbr' => 'pinch',
            ],
            'dash' => [
                'type' => 'volume',
                'ml' => 0.6,
                'abbr' => 'dash',
            ],

            'gram' => [
                'type' => 'weight',
                'gram' => 1,
                'abbr' => 'g',
            ],
            'kilogram' => [
                'type' => 'weight',
                'gram' => 1000,
                'abbr' => 'kg',
            ],
            'pound' => [
                'type' => 'weight',
                'gram' => 453.59237,
                'abbr' => 'Lb',
            ],
            'ounce' => [
                'type' => 'weight',
                'gram' => 28.349523125,
                'abbr' => 'oz',
            ],

            'millimeter' => [
                'type' => 'length',
                'mm' => 1,
                'abbr' => 'mm',
            ],
            'centimeter' => [
                'type' => 'length',
                'mm' => 10,
                'abbr' => 'cm',
            ],
            'inch' => [
                'type' => 'length',
                'mm' => 25.4,
                'abbr' => '"',
            ],

            'quantity' => [
                'type' => 'quantity',
                'abbr' => 'quantity',
            ],
        ];

        foreach($units as $name => $details) {
            DB::table('units')->insert([
                'name' => $name,
                'abbr' => $details['abbr'],
                'type' => $details['type'],
                'ml' => isset($details['ml']) ? $details['ml'] : NULL,
                'gram' => isset($details['gram']) ? $details['gram'] : NULL,
                'mm' => isset($details['mm']) ? $details['mm'] : NULL,
            ]);
        }

    }
}
