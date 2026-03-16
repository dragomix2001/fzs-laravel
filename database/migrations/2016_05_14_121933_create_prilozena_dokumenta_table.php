<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePrilozenaDokumentaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prilozena_dokumenta', function (Blueprint $table) {
            $table->increments('id');
            $table->string('skolskaGodina_id');
            $table->string('naziv');
            $table->integer('redniBrojDokumenta')->unsigned();
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
        Schema::drop('prilozena_dokumenta');
    }
}
