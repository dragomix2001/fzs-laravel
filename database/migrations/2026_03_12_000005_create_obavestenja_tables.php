<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obavestenja', function (Blueprint $table) {
            $table->id();
            $table->string('naslov');
            $table->text('sadrzaj');
            $table->string('tip')->default('opste');
            $table->boolean('aktivan')->default(true);
            $table->datetime('datum_objave')->useCurrent();
            $table->datetime('datum_isteka')->nullable();
            $table->unsignedBigInteger('profesor_id')->nullable();
            $table->timestamps();
            
            $table->index('profesor_id');
        });

        Schema::create('obavestenja_korisnici', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('obavestenje_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('procitano')->default(false);
            $table->datetime('datum_citanja')->nullable();
            $table->timestamps();
            $table->index('obavestenje_id');
            $table->index('user_id');
            $table->unique(['obavestenje_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obavestenja_korisnici');
        Schema::dropIfExists('obavestenja');
    }
};
