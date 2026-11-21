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
        Schema::create('profesor_predmet', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('profesor_id')->nullable();
            $table->integer('predmet_id')->nullable(); // id predmet_program
            $table->integer('oblik_nastave_id')->nullable();
            $table->integer('indikatorAktivan')->unsigned()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('profesor_predmet');
    }
};
