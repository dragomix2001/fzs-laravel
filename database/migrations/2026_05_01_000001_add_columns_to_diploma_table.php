<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diploma', function (Blueprint $table) {
            if (! Schema::hasColumn('diploma', 'brojDiplome')) {
                $table->string('brojDiplome')->nullable();
            }
            if (! Schema::hasColumn('diploma', 'nazivStudijskogPrograma')) {
                $table->string('nazivStudijskogPrograma')->nullable();
            }
            if (! Schema::hasColumn('diploma', 'brojPocetnogLista')) {
                $table->string('brojPocetnogLista')->nullable();
            }
            if (! Schema::hasColumn('diploma', 'brojZapisnika')) {
                $table->string('brojZapisnika')->nullable();
            }
            if (! Schema::hasColumn('diploma', 'datum')) {
                $table->date('datum')->nullable();
            }
            if (! Schema::hasColumn('diploma', 'pristupniRad')) {
                $table->string('pristupniRad')->nullable();
            }
            if (! Schema::hasColumn('diploma', 'tema')) {
                $table->string('tema')->nullable();
            }
            if (! Schema::hasColumn('diploma', 'mentor')) {
                $table->string('mentor')->nullable();
            }
            if (! Schema::hasColumn('diploma', 'ocena')) {
                $table->string('ocena')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('diploma', function (Blueprint $table) {
            $table->dropColumn([
                'brojDiplome',
                'nazivStudijskogPrograma',
                'brojPocetnogLista',
                'brojZapisnika',
                'datum',
                'pristupniRad',
                'tema',
                'mentor',
                'ocena',
            ]);
        });
    }
};
