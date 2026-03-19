<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polozeni_ispiti', function (Blueprint $table) {
            $table->integer('prijava_id')->nullable()->change();
            $table->integer('zapisnik_id')->nullable()->change();
            $table->integer('kandidat_id')->nullable()->change();
            $table->integer('predmet_id')->nullable()->change();
            $table->integer('ocenaPismeni')->nullable()->change();
            $table->integer('ocenaUsmeni')->nullable()->change();
            $table->integer('konacnaOcena')->nullable()->change();
            $table->integer('brojBodova')->nullable()->change();
            $table->integer('statusIspita')->nullable()->change();
            $table->integer('odluka_id')->nullable()->change();
            $table->boolean('indikatorAktivan')->nullable()->change();
        });

        Schema::table('prijava_ispita', function (Blueprint $table) {
            $table->integer('kandidat_id')->nullable()->change();
            $table->integer('predmet_id')->nullable()->change();
            $table->integer('profesor_id')->nullable()->change();
            $table->integer('rok_id')->nullable()->change();
            $table->integer('brojPolaganja')->nullable()->change();
            $table->date('datum')->nullable()->change();
            $table->integer('tipPrijave_id')->nullable()->change();
        });
    }

    public function down(): void
    {
    }
};
