<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kandidat_prilozena_dokumenta', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('indikatorAktivan');
            $table->string('file_name')->nullable()->after('file_path');
            $table->string('mime_type')->nullable()->after('file_name');
            $table->unsignedBigInteger('file_size')->nullable()->after('mime_type');

            $table->index('file_path', 'kpd_file_path_idx');
        });
    }

    public function down(): void
    {
        Schema::table('kandidat_prilozena_dokumenta', function (Blueprint $table) {
            $table->dropIndex('kpd_file_path_idx');
            $table->dropColumn(['file_path', 'file_name', 'mime_type', 'file_size']);
        });
    }
};
