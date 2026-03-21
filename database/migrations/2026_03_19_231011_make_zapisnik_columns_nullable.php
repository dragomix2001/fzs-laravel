<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('zapisnik_o_polaganju_ispita', function (Blueprint $table) {
            if (Schema::hasColumn('zapisnik_o_polaganju_ispita', 'predmet_id')) {
                $table->integer('predmet_id')->nullable()->change();
            }
            if (Schema::hasColumn('zapisnik_o_polaganju_ispita', 'rok_id')) {
                $table->integer('rok_id')->nullable()->change();
            }
            if (Schema::hasColumn('zapisnik_o_polaganju_ispita', 'datum')) {
                $table->date('datum')->nullable()->change();
            }
            if (Schema::hasColumn('zapisnik_o_polaganju_ispita', 'vreme')) {
                $table->time('vreme')->nullable()->change();
            }
            if (Schema::hasColumn('zapisnik_o_polaganju_ispita', 'ucionica')) {
                $table->string('ucionica')->nullable()->change();
            }
            if (Schema::hasColumn('zapisnik_o_polaganju_ispita', 'profesor_id')) {
                $table->integer('profesor_id')->nullable()->change();
            }
            if (Schema::hasColumn('zapisnik_o_polaganju_ispita', 'kandidat_id')) {
                $table->integer('kandidat_id')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zapisnik_o_polaganju_ispita', function (Blueprint $table) {
            //
        });
    }
};
