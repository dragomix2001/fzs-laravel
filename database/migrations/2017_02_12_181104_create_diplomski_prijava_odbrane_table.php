<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('diplomski_prijava_odbrane', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tipStudija_id');
            $table->integer('studijskiProgram_id');
            $table->integer('kandidat_id');
            $table->integer('predmet_id');
            $table->string('nazivTeme');
            $table->date('datumPrijave');
            $table->date('datumOdbrane');
            $table->integer('temu_odobrio_profesor_id');
            $table->integer('odbranu_odobrio_profesor_id');
            $table->boolean('indikatorOdobreno');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('diplomski_prijava_odbrane');
    }
};
