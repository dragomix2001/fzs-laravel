<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nastavne_nedelje', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('skolska_godina_id');
            $table->integer('redni_broj');
            $table->date('datum_pocetka');
            $table->date('datum_kraja');
            $table->timestamps();

            $table->index('skolska_godina_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nastavne_nedelje');
    }
};
