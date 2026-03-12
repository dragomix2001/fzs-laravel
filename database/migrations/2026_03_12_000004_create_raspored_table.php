<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            $table->tinyInteger('aktivan')->default(1);
            
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
        
        // Seed data - only if tables have data
        if (DB::table('predmet')->count() > 0 && DB::table('skolska_god_upisa')->count() > 0) {
            $predmet = DB::table('predmet')->first();
            $profesor = DB::table('profesor')->first();
            $program = DB::table('studijski_program')->first();
            $godina = DB::table('godina_studija')->first();
            $semestar = DB::table('semestar')->first();
            $obl = DB::table('oblik_nastave')->first();
            $sk = DB::table('skolska_god_upisa')->where('aktivan', 1)->first();
            
            if ($predmet && $profesor && $program && $godina && $semestar && $obl && $sk) {
                DB::table('raspored')->insert([
                    ['predmet_id' => $predmet->id, 'profesor_id' => $profesor->id, 'studijski_program_id' => $program->id, 'godina_studija_id' => $godina->id, 'semestar_id' => $semestar->id, 'skolska_godina_id' => $sk->id, 'oblik_nastave_id' => $obl->id, 'dan' => 1, 'vreme_od' => '09:00:00', 'vreme_do' => '11:00:00', 'prostorija' => 'A-101', 'grupa' => 'Svi', 'aktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
                    ['predmet_id' => $predmet->id, 'profesor_id' => $profesor->id, 'studijski_program_id' => $program->id, 'godina_studija_id' => $godina->id, 'semestar_id' => $semestar->id, 'skolska_godina_id' => $sk->id, 'oblik_nastave_id' => $obl->id, 'dan' => 3, 'vreme_od' => '14:00:00', 'vreme_do' => '16:00:00', 'prostorija' => 'Lab-1', 'grupa' => 'A', 'aktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('raspored');
    }
};
