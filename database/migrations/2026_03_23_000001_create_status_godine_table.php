<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_godine', function (Blueprint $table) {
            $table->id();
            $table->string('naziv')->nullable();
            $table->dateTime('datum')->nullable();
            $table->unsignedInteger('indikatorAktivan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_godine');
    }
};
