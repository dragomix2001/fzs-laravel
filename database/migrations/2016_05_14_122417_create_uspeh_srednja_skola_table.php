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
        Schema::create('uspeh_srednja_skola', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('kandidat_id')->unsigned()->index();
            $table->integer('srednjeSkoleFakulteti_id')->unsigned()->index();
            $table->integer('opstiUspeh_id')->unsigned()->index();
            $table->double('srednja_ocena');
            $table->integer('RedniBrojRazreda');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('uspeh_srednja_skola');
    }
};
