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
            $table->foreignId('student_id')->constrained('kandidat')->onDelete('cascade');
            $table->foreignId('predmet_id')->constrained()->onDelete('cascade');
            $table->foreignId('nastavna_nedelja_id')->constrained('nastavne_nedelje')->onDelete('cascade');
            $table->enum('status', ['prisutan', 'odsutan', 'opravdan', 'kasnio'])->default('odsutan');
            $table->text('napomena')->nullable();
            $table->foreignId('profesor_id')->constrained()->onDelete('set null');
            $table->timestamps();
            
            $table->unique(['student_id', 'predmet_id', 'nastavna_nedelja_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prisanstva');
    }
};
