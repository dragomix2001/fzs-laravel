<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('predmet', function (Blueprint $table) {
            $table->index('naziv', 'predmet_naziv_search_index');
        });

        Schema::table('predmet_program', function (Blueprint $table) {
            $table->index('predmet_id', 'predmet_program_predmet_search_index');
        });
    }

    public function down(): void
    {
        Schema::table('predmet_program', function (Blueprint $table) {
            $table->dropIndex('predmet_program_predmet_search_index');
        });

        Schema::table('predmet', function (Blueprint $table) {
            $table->dropIndex('predmet_naziv_search_index');
        });
    }
};
