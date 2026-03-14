<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skolska_god_upisa', function (Blueprint $table) {
            $table->tinyInteger('aktivan')->default(1)->after('naziv');
        });
    }

    public function down(): void
    {
        Schema::table('skolska_god_upisa', function (Blueprint $table) {
            $table->dropColumn('aktivan');
        });
    }
};
