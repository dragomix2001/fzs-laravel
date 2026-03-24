<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polozeni_ispiti', function (Blueprint $table) {
            if (Schema::hasColumn('polozeni_ispiti', 'prijava_id')) {
                $table->integer('prijava_id')->nullable()->change();
            }
            if (Schema::hasColumn('polozeni_ispiti', 'zapisnik_id')) {
                $table->integer('zapisnik_id')->nullable()->change();
            }
            if (Schema::hasColumn('polozeni_ispiti', 'kandidat_id')) {
                $table->integer('kandidat_id')->nullable()->change();
            }
            if (Schema::hasColumn('polozeni_ispiti', 'predmet_id')) {
                $table->integer('predmet_id')->nullable()->change();
            }
            if (Schema::hasColumn('polozeni_ispiti', 'ocenaPismeni')) {
                $table->integer('ocenaPismeni')->nullable()->change();
            }
            if (Schema::hasColumn('polozeni_ispiti', 'ocenaUsmeni')) {
                $table->integer('ocenaUsmeni')->nullable()->change();
            }
            if (Schema::hasColumn('polozeni_ispiti', 'konacnaOcena')) {
                $table->integer('konacnaOcena')->nullable()->change();
            }
            if (Schema::hasColumn('polozeni_ispiti', 'brojBodova')) {
                $table->integer('brojBodova')->nullable()->change();
            }
            if (Schema::hasColumn('polozeni_ispiti', 'statusIspita')) {
                $table->integer('statusIspita')->nullable()->change();
            }
            if (Schema::hasColumn('polozeni_ispiti', 'odluka_id')) {
                $table->integer('odluka_id')->nullable()->change();
            }
            if (Schema::hasColumn('polozeni_ispiti', 'indikatorAktivan')) {
                $table->boolean('indikatorAktivan')->nullable()->change();
            }
        });

        Schema::table('prijava_ispita', function (Blueprint $table) {
            if (Schema::hasColumn('prijava_ispita', 'kandidat_id')) {
                $table->integer('kandidat_id')->nullable()->change();
            }
            if (Schema::hasColumn('prijava_ispita', 'predmet_id')) {
                $table->integer('predmet_id')->nullable()->change();
            }
            if (Schema::hasColumn('prijava_ispita', 'profesor_id')) {
                $table->integer('profesor_id')->nullable()->change();
            }
            if (Schema::hasColumn('prijava_ispita', 'rok_id')) {
                $table->integer('rok_id')->nullable()->change();
            }
            if (Schema::hasColumn('prijava_ispita', 'brojPolaganja')) {
                $table->integer('brojPolaganja')->nullable()->change();
            }
            if (Schema::hasColumn('prijava_ispita', 'datum')) {
                $table->date('datum')->nullable()->change();
            }
            if (Schema::hasColumn('prijava_ispita', 'tipPrijave_id')) {
                $table->integer('tipPrijave_id')->nullable()->change();
            }
        });
    }

    public function down(): void {}
};
