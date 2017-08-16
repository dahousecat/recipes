<?php

use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::table('users')->insert([
            'name' => 'Felix',
            'email' => 'dahousecat@gmail.com',
            'password' => bcrypt('password'),
            'api_token' => str_random(60),
        ]);

        foreach(range(1, 10) as $i) {
        	User::create([
        		'name' => $faker->name,
        		'email' => $faker->email,
        		'password' => bcrypt('password'),
        		'api_token' => str_random(60)
        	]);
        }
    }
}
