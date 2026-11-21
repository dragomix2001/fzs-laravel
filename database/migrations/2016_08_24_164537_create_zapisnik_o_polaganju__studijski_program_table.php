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
        Schema::create('zapisnik_o_polaganju__studijski_program', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('zapisnik_id');
            $table->integer('StudijskiProgram_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('zapisnik_o_polaganju__studijski_program');
    }
};
