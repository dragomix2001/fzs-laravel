<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raspored', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('predmet_id');
            $table->unsignedBigInteger('profesor_id');
            $table->unsignedBigInteger('studijski_program_id');
            $table->unsignedBigInteger('godina_studija_id');
            $table->unsignedBigInteger('semestar_id');
            $table->unsignedBigInteger('skolska_godina_id');
            $table->unsignedBigInteger('oblik_nastave_id');
            
            $table->tinyInteger('dan');
            $table->time('vreme_od');
            $table->time('vreme_do');
            $table->string('prostorija', 50)->nullable();
            $table->string('grupa', 50)->nullable();
            
            $table->timestamps();
            
            $table->index('predmet_id');
            $table->index('profesor_id');
            $table->index('studijski_program_id');
            $table->index('godina_studija_id');
            $table->index('semestar_id');
            $table->index('skolska_godina_id');
            $table->index('oblik_nastave_id');
            $table->index('dan');
            
            $table->unique([
                'predmet_id', 
                'studijski_program_id', 
                'godina_studija_id', 
                'semestar_id', 
                'skolska_godina_id', 
                'dan', 
                'vreme_od',
                'grupa'
            ], 'raspored_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raspored');
    }
};
