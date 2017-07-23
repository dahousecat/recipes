<?php

use Illuminate\Database\Seeder;
use App\Models\Recipe;
use App\Models\Ingredient;
use App\Models\Unit;

class RecipesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $recipes = json_decode(file_get_contents(__DIR__ . '/recipeTableSeederData.json'));

        foreach($recipes as $i => $data) {
            $recipe = Recipe::create([
                'user_id' => 1,
                'name' => $data->name,
                'description' => $data->description,
            ]);

            foreach($data->rows as $row) {

                \DB::enableQueryLog();

                $ingredient = Ingredient::loadByName($row->ingredient);
                $unit = Unit::loadByName($row->unit);

                if(empty($ingredient)) {
                    echo $row->ingredient . ' not found';

                    d(\DB::getQueryLog());

                    // Raspberries, frozen
                    // Raspberries, frozen
                }

                $recipe->rows()->create([
                    'ingredient_id' => $ingredient->id,
                    'delta' => $row->delta,
                    'unit_id' => $unit->id,
                    'value' => $row->value,
                    'weight' => empty($row->weight) ? 0 : $row->weight,
                ]);
            }
        }
    }
}
