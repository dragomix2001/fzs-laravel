<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zapisnik_o_polaganju_ispita', function (Blueprint $table) {
            $table->integer('kandidat_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('zapisnik_o_polaganju_ispita', function (Blueprint $table) {
            $table->integer('kandidat_id')->change();
        });
    }
};
