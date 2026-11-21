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
        Schema::create('aktivni_ispitni_rokovi', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('rok_id');
            $table->string('naziv');
            $table->date('pocetak');
            $table->date('kraj');
            $table->integer('tipRoka_id');
            $table->string('komentar');
            $table->boolean('indikatorAktivan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('aktivni_ispitni_rokovi');
    }
};
