<?php

namespace App\Console\Commands;

use App\Models\Recipe;
use Illuminate\Console\Command;
use App\Models\Unit;

class GenerateRecipeSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:recipeSeeder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an recipe seeder from what is currently in the DB';

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
        $recipes = Recipe::with(
            'rows.ingredient',
            'rows.unit',
            'directions'
        )->get()->toArray();

        foreach($recipes as &$recipe) {
            unset($recipe['id']);
            unset($recipe['created_at']);
            unset($recipe['updated_at']);
            unset($recipe['user_id']);
            unset($recipe['image']);

            $rows = [];
            foreach($recipe['rows'] as &$row) {
                $rows[] = [
                    'ingredient' => $row['ingredient']['name'],
                    'delta'      => $row['delta'],
                    'unit'       => $row['unit']['name'],
                    'value'      => $row['value'],
                    'weight'     => $row['weight'],
                ];
            }
            $recipe['rows'] = $rows;

        }
        $filename = base_path() . '/database/seeds/recipeTableSeederData.json';
        $data = json_encode($recipes);
        file_put_contents($filename, $data);
    }

}
