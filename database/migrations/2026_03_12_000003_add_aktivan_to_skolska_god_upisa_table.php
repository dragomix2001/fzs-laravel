<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('skolska_god_upisa', function (Blueprint $table) {
            $table->tinyInteger('aktivan')->default(0)->after('naziv');
        });

        // Seed data - set first record as active
        DB::table('skolska_god_upisa')->where('id', 1)->update(['aktivan' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skolska_god_upisa', function (Blueprint $table) {
            $table->dropColumn('aktivan');
        });
    }
};
