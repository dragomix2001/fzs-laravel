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
            if (!Schema::hasColumn('zapisnik_o_polaganju_ispita', 'datum2')) {
                $table->date('datum2')->nullable()->after('datum');
            }
            
            if (!Schema::hasColumn('zapisnik_o_polaganju_ispita', 'prijavaIspita_id')) {
                $table->integer('prijavaIspita_id')->nullable()->after('kandidat_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zapisnik_o_polaganju_ispita', function (Blueprint $table) {
            if (Schema::hasColumn('zapisnik_o_polaganju_ispita', 'datum2')) {
                $table->dropColumn('datum2');
            }
            
            if (Schema::hasColumn('zapisnik_o_polaganju_ispita', 'prijavaIspita_id')) {
                $table->dropColumn('prijavaIspita_id');
            }
        });
    }
};
