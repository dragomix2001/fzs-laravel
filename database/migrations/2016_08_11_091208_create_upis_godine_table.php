<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('upis_godine', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('kandidat_id');
            $table->integer('godina');
            $table->integer('pokusaj');
            $table->integer('tipStudija_id');
            $table->integer('statusGodine_id');
            $table->integer('studijskiProgram_id');
            $table->integer('skolskaGodina_id')->nullable();
            $table->date('datumUpisa')->nullable();
            $table->date('datumPromene')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('upis_godine');
    }
};
