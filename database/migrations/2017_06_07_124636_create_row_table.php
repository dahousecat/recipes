<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRowTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('row', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('recipe_id')->unsigned();
//            $table->foreign('recipe_id')->references('id')->on('recipes');
            $table->integer('ingredient_id')->unsigned();
//            $table->foreign('ingredient_id')->references('id')->on('ingredients');
            $table->integer('delta')->unsigned();
            $table->integer('unit_id')->unsigned();
//            $table->foreign('unit_id')->references('id')->on('units');
            $table->integer('unit_value')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('row');
    }
}
