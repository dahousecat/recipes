<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIngredientUnitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ingredient_unit', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ingredient_id')->unsigned();
//            $table->foreign('ingredient_id')->references('id')->on('ingredients');
            $table->integer('unit_id')->unsigned();
//            $table->foreign('unit_id')->references('id')->on('units');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ingredient_unit');
    }
}
