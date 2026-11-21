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
        Schema::create('godina_studija', function (Blueprint $table) {
            $table->increments('id');
            $table->string('naziv');
            $table->string('nazivRimski');
            $table->string('nazivSlovimaUPadezu');
            $table->integer('redosledPrikazivanja')->unsigned();
            $table->integer('indikatorAktivan')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('godina_studija');
    }
};
