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
        Schema::create('prijava_ispita', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('kandidat_id');
            $table->integer('predmet_id');
            $table->integer('profesor_id');
            $table->integer('rok_id');
            $table->integer('brojPolaganja');
            $table->date('datum');
            $table->date('datum2');
            $table->time('vreme');
            $table->integer('tipPrijave_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('prijava_ispita');
    }
};
