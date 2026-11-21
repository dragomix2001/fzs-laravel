<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('diploma', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('kandidat_id')->nullable();
            $table->dateTime('datumOdbrane')->nullable();
            $table->string('broj')->nullable();
            $table->integer('lice')->nullable();
            $table->string('funkcija')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diploma');
    }
};
