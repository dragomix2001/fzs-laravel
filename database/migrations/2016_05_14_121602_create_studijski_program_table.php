<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStudijskiProgramTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('studijski_program', function (Blueprint $table) {
            $table->increments('id');
            $table->string('naziv');
            $table->string('skrNazivStudijskogPrograma');
            $table->string('zvanje');
            $table->integer('tipStudija_id')->unsigned()->index();
            $table->integer('indikatorAktivan')->unsigned();
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
        Schema::drop('studijski_program');
    }
}
