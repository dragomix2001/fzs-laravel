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
        Schema::create('uplata_skolarine', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('skolarina_id');
            $table->integer('kandidat_id');
            $table->double('iznos');
            $table->string('naziv')->nullable();
            $table->date('datum');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('uplata_skolarine');
    }
};
