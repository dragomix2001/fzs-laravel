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
        Schema::create('srednje_skole_fakulteti', function (Blueprint $table) {
            $table->increments('id');
            $table->string('naziv');
            $table->integer('indSkoleFakulteta');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('srednje_skole_fakulteti');
    }
};
