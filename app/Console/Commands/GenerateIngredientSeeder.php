<?php

namespace App\Console\Commands;

use App\Models\Ingredient;
use Illuminate\Console\Command;
use App\Models\Unit;

class GenerateIngredientSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:ingredientSeeder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an ingredient seeder from what is currently in the DB';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ingredients = Ingredient::with('units', 'attributes.attributeType')->get()->toArray();
        foreach($ingredients as &$ingredient) {
            unset($ingredient['id']);
            unset($ingredient['created_at']);
            unset($ingredient['updated_at']);
            unset($ingredient['user_id']);
            unset($ingredient['image']);
            unset($ingredient['description']);

            $default_unit = Unit::find($ingredient['default_unit_id']);
            if(!empty($default_unit)) {
                $ingredient['default_unit'] = $default_unit->name;
            }

            $units = [];
            foreach($ingredient['units'] as &$unit) {
                $units[] = $unit['name'];
            }
            $ingredient['units'] = $units;

            $attributes = [];
            foreach($ingredient['attributes'] as &$attribute) {
                $attributes[] = [
                    'value' => $attribute['value'],
                    'type' => $attribute['attribute_type']['safe_name'],
                ];
            }
            $ingredient['attributes'] = $attributes;
        }

        $filename = base_path() . '/database/seeds/ingredientTableSeederData.json';
        $data = json_encode($ingredients);
        file_put_contents($filename, $data);
    }

}
