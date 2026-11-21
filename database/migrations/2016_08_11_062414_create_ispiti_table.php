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
        Schema::create('ispiti', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('predmet_id');
            $table->integer('student_id');
            $table->integer('ispitni_rok_id');
            $table->integer('ocena');
            $table->string('godina');
            $table->integer('polozen')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('ispiti');
    }
};
