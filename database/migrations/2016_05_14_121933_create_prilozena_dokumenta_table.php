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
        Schema::create('prilozena_dokumenta', function (Blueprint $table) {
            $table->increments('id');
            $table->string('skolskaGodina_id');
            $table->string('naziv');
            $table->integer('redniBrojDokumenta')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('prilozena_dokumenta');
    }
};
