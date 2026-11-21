<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('aktivni_ispitni_rokovi', function (Blueprint $table) {
            $table->index(
                ['indikatorAktivan', 'kraj', 'pocetak'],
                'aktivni_rokovi_dashboard_deadline_index'
            );
        });

        Schema::table('prijava_ispita', function (Blueprint $table) {
            $table->index('datum', 'prijava_ispita_dashboard_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('prijava_ispita', function (Blueprint $table) {
            $table->dropIndex('prijava_ispita_dashboard_date_index');
        });

        Schema::table('aktivni_ispitni_rokovi', function (Blueprint $table) {
            $table->dropIndex('aktivni_rokovi_dashboard_deadline_index');
        });
    }
};
