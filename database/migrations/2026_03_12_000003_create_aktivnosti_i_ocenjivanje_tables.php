<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aktivnosti', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('predmet_id');
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

            $table->index('predmet_id');
        });

        Schema::create('ocenjivanje', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('aktivnost_id');
            $table->decimal('bodovi', 5, 2)->nullable();
            $table->decimal('ocena', 3, 2)->nullable();
            $table->text('napomena')->nullable();
            $table->unsignedBigInteger('profesor_id')->nullable();
            $table->timestamps();

            $table->index('student_id');
            $table->index('aktivnost_id');
            $table->unique(['student_id', 'aktivnost_id']);
        });

        // Seed data
        if (DB::table('predmet')->count() > 0) {
            $predmet = DB::table('predmet')->first();
            if ($predmet) {
                DB::table('aktivnosti')->insert([
                    ['predmet_id' => $predmet->id, 'naziv' => 'Kolokvijum 1', 'tip' => 'kolokvijum', 'max_bodova' => 30, 'prolaz_bodova' => 15, 'datum' => '2025-11-15', 'vreme_pocetka' => '10:00:00', 'ucionica' => 'A-101', 'aktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
                    ['predmet_id' => $predmet->id, 'naziv' => 'Seminarski rad', 'tip' => 'seminarski', 'max_bodova' => 20, 'prolaz_bodova' => 10, 'datum' => '2025-12-20', 'vreme_pocetka' => null, 'ucionica' => null, 'aktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ocenjivanje');
        Schema::dropIfExists('aktivnosti');
    }
};
