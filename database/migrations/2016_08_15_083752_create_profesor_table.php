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
        Schema::create('profesor', function (Blueprint $table) {
            $table->increments('id');
            $table->string('jmbg');
            $table->string('ime')->nullable();
            $table->string('prezime')->nullable();
            $table->string('telefon')->nullable();
            $table->integer('status_id')->nullable();
            $table->string('zvanje')->nullable();
            $table->string('kabinet')->nullable();
            $table->string('mail')->nullable();
            $table->integer('indikatorAktivan')->unsigned()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('profesor');
    }
};
