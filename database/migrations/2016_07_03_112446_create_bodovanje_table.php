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
        Schema::create('bodovanje', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('poeniMax')->nullable();
            $table->integer('poeniMin')->nullable();
            $table->integer('ocena')->nullable();
            $table->string('opisnaOcena')->nullable();
            $table->integer('indikatorAktivan')->unsigned()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('bodovanje');
    }
};
