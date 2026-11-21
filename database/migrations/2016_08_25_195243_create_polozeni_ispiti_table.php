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
        Schema::create('polozeni_ispiti', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('prijava_id');
            $table->integer('zapisnik_id');
            $table->integer('kandidat_id');
            $table->integer('predmet_id');
            $table->integer('ocenaPismeni');
            $table->integer('ocenaUsmeni');
            $table->integer('konacnaOcena');
            $table->integer('brojBodova');
            $table->integer('statusIspita');
            $table->integer('odluka_id');
            $table->boolean('indikatorAktivan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('polozeni_ispiti');
    }
};
