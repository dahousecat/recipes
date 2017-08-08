<?php

use Illuminate\Database\Seeder;
use App\Models\Ingredient;
use Faker\Factory;
use App\Models\Unit;
use App\Models\Attribute;
Use App\Models\AttributeType;

class IngredientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ingredients = json_decode(file_get_contents(__DIR__ . '/ingredientTableSeederData.json'));

        $units_by_type = [];
        $user_id = 1;

        foreach($ingredients as $i => $data) {

            $default_unit = Unit::loadByName($data->default_unit);
            $ingredient = Ingredient::create([
                'name' => $data->name,
                'weight_one' => $data->weight_one,
                'weight_one_cm' => $data->weight_one_cm,
                'weight_one_cup' => $data->weight_one_cup,
                'default_unit_id' => empty($default_unit) ? NULL : $default_unit->id,
                'user_id' => $user_id,
            ]);

            foreach($data->units as $unit_type) {
                if(!isset($units_by_type[$unit_type])) {
                    $units_by_type[$unit_type] = Unit::loadByName($unit_type);
                }
                if($units_by_type[$unit_type]) {
                    $ingredient->units()->save($units_by_type[$unit_type]);
                }
            }

            foreach($data->attributes as $attribute) {
                $attribute_type = AttributeType::loadByName($attribute->type);
                Attribute::create([
                    'ingredient_id'     => $ingredient->id,
                    'value'             => $attribute->value,
                    'attribute_type_id' => $attribute_type->id,
                ]);

            }

        }
    }
}
