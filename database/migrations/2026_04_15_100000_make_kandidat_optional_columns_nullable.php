<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kandidat', function (Blueprint $table) {
            $table->integer('krsnaSlava_id')->unsigned()->nullable()->change();
            $table->integer('uspehSrednjaSkola_id')->unsigned()->nullable()->change();
            $table->integer('opstiUspehSrednjaSkola_id')->unsigned()->nullable()->change();
            $table->integer('indikatorAktivan')->unsigned()->nullable()->default(0)->change();
            $table->integer('mesto_id')->unsigned()->nullable()->change();
            $table->integer('godinaStudija_id')->unsigned()->nullable()->change();
            $table->integer('skolskaGodinaUpisa_id')->unsigned()->nullable()->change();
        });

        Schema::table('predmet_program', function (Blueprint $table) {
            $table->integer('godinaStudija_id')->unsigned()->nullable()->change();
            $table->integer('semestar')->unsigned()->nullable()->change();
            $table->integer('tipPredmeta_id')->nullable()->change();
            $table->integer('espb')->nullable()->change();
            $table->integer('statusPredmeta')->nullable()->change();
            $table->integer('predavanja')->nullable()->change();
            $table->integer('vezbe')->nullable()->change();
            $table->integer('skolskaGodina_id')->nullable()->change();
        });

        Schema::table('godina_studija', function (Blueprint $table) {
            $table->string('nazivRimski')->nullable()->change();
            $table->string('nazivSlovimaUPadezu')->nullable()->change();
            $table->integer('redosledPrikazivanja')->unsigned()->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('kandidat', function (Blueprint $table) {
            $table->integer('krsnaSlava_id')->unsigned()->nullable(false)->change();
            $table->integer('uspehSrednjaSkola_id')->unsigned()->nullable(false)->change();
            $table->integer('opstiUspehSrednjaSkola_id')->unsigned()->nullable(false)->change();
            $table->integer('indikatorAktivan')->unsigned()->nullable(false)->change();
            $table->integer('mesto_id')->unsigned()->nullable(false)->change();
            $table->integer('godinaStudija_id')->unsigned()->nullable(false)->change();
            $table->integer('skolskaGodinaUpisa_id')->unsigned()->nullable(false)->change();
        });

        Schema::table('predmet_program', function (Blueprint $table) {
            $table->integer('godinaStudija_id')->unsigned()->nullable(false)->change();
            $table->integer('semestar')->unsigned()->nullable(false)->change();
            $table->integer('tipPredmeta_id')->nullable(false)->change();
            $table->integer('espb')->nullable(false)->change();
            $table->integer('statusPredmeta')->nullable(false)->change();
            $table->integer('predavanja')->nullable(false)->change();
            $table->integer('vezbe')->nullable(false)->change();
            $table->integer('skolskaGodina_id')->nullable(false)->change();
        });

        Schema::table('godina_studija', function (Blueprint $table) {
            $table->string('nazivRimski')->nullable(false)->change();
            $table->string('nazivSlovimaUPadezu')->nullable(false)->change();
            $table->integer('redosledPrikazivanja')->unsigned()->nullable(false)->change();
        });
    }
};
