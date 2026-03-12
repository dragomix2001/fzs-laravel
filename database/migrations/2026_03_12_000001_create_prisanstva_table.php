<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prisanstva', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('predmet_id');
            $table->unsignedBigInteger('nastavna_nedelja_id');
            $table->enum('status', ['prisutan', 'odsutan', 'opravdan', 'kasnio'])->default('odsutan');
            $table->text('napomena')->nullable();
            $table->unsignedBigInteger('profesor_id')->nullable();
            $table->timestamps();
            
            $table->index('student_id');
            $table->index('predmet_id');
            $table->index('nastavna_nedelja_id');
            $table->unique(['student_id', 'predmet_id', 'nastavna_nedelja_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prisanstva');
    }
};
