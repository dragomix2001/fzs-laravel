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
        Schema::create('tip_studija', function (Blueprint $table) {
            $table->increments('id');
            $table->string('naziv');
            $table->string('opis')->nullable();
            $table->string('skrNaziv');
            $table->integer('indikatorAktivan')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('tip_studija');
    }
};
