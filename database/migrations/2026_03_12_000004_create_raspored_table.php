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
            $table->foreignId('predmet_id')->constrained('predmet')->onDelete('cascade');
            $table->foreignId('profesor_id')->constrained('profesor')->onDelete('cascade');
            $table->foreignId('studijski_program_id')->constrained('studijski_program')->onDelete('cascade');
            $table->foreignId('godina_studija_id')->constrained('godina_studija')->onDelete('cascade');
            $table->foreignId('semestar_id')->constrained('semestar')->onDelete('cascade');
            $table->foreignId('skolska_godina_id')->constrained('skolska_god_upisa')->onDelete('cascade');
            $table->foreignId('oblik_nastave_id')->constrained('oblik_nastave')->onDelete('cascade');
            
            // Dan u nedelji (1 = Ponedeljak, 7 = Nedelja)
            $table->tinyInteger('dan');
            
            // Vreme
            $table->time('vreme_od');
            $table->time('vreme_do');
            
            // Prostorija
            $table->string('prostorija', 50)->nullable();
            
            // Grupa (optional - za podelu studenata na grupe)
            $table->string('grupa', 50)->nullable();
            
            $table->timestamps();
            
            // Unique constraint to prevent duplicate schedule entries
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
