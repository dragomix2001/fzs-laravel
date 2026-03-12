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
            $table->foreignId('skolska_godina_id')->constrained('skolska_god_upisa')->onDelete('cascade');
            $table->integer('redni_broj');
            $table->date('datum_pocetka');
            $table->date('datum_kraja');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nastavne_nedelje');
    }
};
