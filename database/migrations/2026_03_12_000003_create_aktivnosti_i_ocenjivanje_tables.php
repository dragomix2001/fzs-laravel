<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aktivnosti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('predmet_id')->constrained()->onDelete('cascade');
            $table->string('naziv');
            $table->enum('tip', ['kolokvijum', 'seminarski', 'projekat', 'prakticni', 'usmeni']);
            $table->decimal('max_bodova', 5, 2)->default(100);
            $table->decimal('prolaz_bodova', 5, 2)->default(50);
            $table->date('datum');
            $table->time('vreme_pocetka')->nullable();
            $table->string('ucionica')->nullable();
            $table->text('napomena')->nullable();
            $table->boolean('aktivan')->default(true);
            $table->timestamps();
        });

        Schema::create('ocenjivanje', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('kandidat')->onDelete('cascade');
            $table->foreignId('aktivnost_id')->constrained('aktivnosti')->onDelete('cascade');
            $table->decimal('bodovi', 5, 2)->nullable();
            $table->decimal('ocena', 3, 2)->nullable();
            $table->text('napomena')->nullable();
            $table->foreignId('profesor_id')->constrained()->onDelete('set null');
            $table->timestamps();
            
            $table->unique(['student_id', 'aktivnost_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ocenjivanje');
        Schema::dropIfExists('aktivnosti');
    }
};
