<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributeIngredientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attribute_ingredient', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ingredient_id')->unsigned();
//            $table->foreign('ingredient_id')->references('id')->on('ingredients');
            $table->integer('attribute_id')->unsigned();
//            $table->foreign('attribute_id')->references('id')->on('attributes');
            $table->float('value', 8, 2)->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attribute_ingredient');
    }
}
