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
        Schema::create('skolarina', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('kandidat_id');
            $table->double('iznos');
            $table->string('komentar');
            $table->integer('godinaStudija_id');
            $table->integer('tipStudija_id');
            $table->integer('studijskiProgram_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('skolarina');
    }
};
