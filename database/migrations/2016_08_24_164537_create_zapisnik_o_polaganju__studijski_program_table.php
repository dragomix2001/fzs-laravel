<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateZapisnikOPolaganjuStudijskiProgramTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zapisnik_o_polaganju__studijski_program', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('zapisnik_id');
            $table->integer('StudijskiProgram_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('zapisnik_o_polaganju__studijski_program');
    }
}
