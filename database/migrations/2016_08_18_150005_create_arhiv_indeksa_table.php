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
        Schema::create('arhiv_indeksa', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('indeks');
            $table->integer('tipStudija_id');
            $table->integer('skolskaGodinaUpisa_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('arhiv_indeksa');
    }
};
