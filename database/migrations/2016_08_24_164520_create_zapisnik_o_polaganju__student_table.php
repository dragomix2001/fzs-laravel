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
        Schema::create('zapisnik_o_polaganju__student', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('kandidat_id');
            $table->integer('prijavaIspita_id');
            $table->integer('zapisnik_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('zapisnik_o_polaganju__student');
    }
};
