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
        Schema::create('zapisnik_o_polaganju_ispita', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('predmet_id');
            $table->integer('rok_id');
            $table->integer('prijavaIspita_id');
            $table->date('datum');
            $table->date('datum2');
            $table->time('vreme');
            $table->string('ucionica');
            $table->integer('profesor_id');
            $table->integer('kandidat_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('zapisnik_o_polaganju_ispita');
    }
};
