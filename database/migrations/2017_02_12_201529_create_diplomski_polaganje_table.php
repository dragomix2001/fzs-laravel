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
        Schema::create('diplomski_polaganje', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tipStudija_id');
            $table->integer('studijskiProgram_id');
            $table->integer('kandidat_id');
            $table->integer('predmet_id');
            $table->string('nazivTeme');
            $table->date('datum');
            $table->time('vreme');
            $table->integer('profesor_id');
            $table->integer('profesor_id_predsednik');
            $table->integer('profesor_id_clan');
            $table->integer('rok_id');
            $table->integer('brojBodova')->nullable();
            $table->integer('ocena')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('diplomski_polaganje');
    }
};
