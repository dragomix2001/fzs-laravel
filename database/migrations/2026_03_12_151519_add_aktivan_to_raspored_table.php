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
        Schema::table('raspored', function (Blueprint $table) {
            $table->tinyInteger('aktivan')->default(1)->after('grupa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('raspored', function (Blueprint $table) {
            $table->dropColumn('aktivan');
        });
    }
};
