<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->increments('id');
//            $table->foreign('unit_id')->references('id')->on('units');
            $table->integer('ingredient_id')->unsigned();
//            $table->foreign('ingredient_id')->references('id')->on('ingredients');
            $table->float('value', 10, 5)->unsigned()->nullable();
            $table->integer('attribute_type_id')->unsigned();
//            $table->foreign('attribute_type_id')->references('id')->on('attribute_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attributes');
    }
}
