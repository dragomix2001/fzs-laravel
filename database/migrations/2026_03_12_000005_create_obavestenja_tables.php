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
            $table->foreignId('profesor_id')->nullable()->constrained('profesor')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('obavestenja_korisnici', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obavestenje_id')->constrained('obavestenja')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('procitano')->default(false);
            $table->datetime('datum_citanja')->nullable();
            $table->timestamps();
            $table->unique(['obavestenje_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obavestenja_korisnici');
        Schema::dropIfExists('obavestenja');
    }
};
