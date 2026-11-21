<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
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
     */
    public function down(): void
    {
        Schema::drop('studijski_program');
    }
};
