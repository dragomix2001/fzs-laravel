<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zapisnik_o_polaganju_ispita', function (Blueprint $table) {
            if (Schema::hasColumn('zapisnik_o_polaganju_ispita', 'prijavaIspita_id')) {
                $table->unsignedInteger('prijavaIspita_id')->nullable()->change();
            }
            if (Schema::hasColumn('zapisnik_o_polaganju_ispita', 'datum2')) {
                $table->date('datum2')->nullable()->change();
            }
        });

        Schema::table('prijava_ispita', function (Blueprint $table) {
            if (Schema::hasColumn('prijava_ispita', 'datum2')) {
                $table->date('datum2')->nullable()->change();
            }
            if (Schema::hasColumn('prijava_ispita', 'vreme')) {
                $table->time('vreme')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('zapisnik_o_polaganju_ispita', function (Blueprint $table) {
            if (Schema::hasColumn('zapisnik_o_polaganju_ispita', 'prijavaIspita_id')) {
                $table->unsignedInteger('prijavaIspita_id')->nullable(false)->change();
            }
            if (Schema::hasColumn('zapisnik_o_polaganju_ispita', 'datum2')) {
                $table->date('datum2')->nullable(false)->change();
            }
        });

        Schema::table('prijava_ispita', function (Blueprint $table) {
            if (Schema::hasColumn('prijava_ispita', 'datum2')) {
                $table->date('datum2')->nullable(false)->change();
            }
            if (Schema::hasColumn('prijava_ispita', 'vreme')) {
                $table->time('vreme')->nullable(false)->change();
            }
        });
    }
};
