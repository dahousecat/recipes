<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rows', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('recipe_id')->unsigned();
//            $table->foreign('recipe_id')->references('id')->on('recipes');
            $table->integer('ingredient_id')->unsigned();
//            $table->foreign('ingredient_id')->references('id')->on('ingredients');
            $table->integer('delta')->unsigned();
            $table->integer('unit_id')->unsigned();
//            $table->foreign('unit_id')->references('id')->on('units');
            $table->integer('value')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rows');
    }
}
