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
            $table->integer('predmet_id')->nullable()->change();
            $table->integer('rok_id')->nullable()->change();
            $table->date('datum')->nullable()->change();
            $table->time('vreme')->nullable()->change();
            $table->string('ucionica')->nullable()->change();
            $table->integer('profesor_id')->nullable()->change();
            $table->integer('kandidat_id')->nullable()->change();
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
