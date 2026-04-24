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
            $table->string('review_status')->default('pending');
            $table->unsignedInteger('reviewer_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->index('reviewer_id', 'kpd_reviewer_id_idx');
            $table->index(['kandidat_id', 'review_status'], 'kpd_kandidat_review_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('kandidat_prilozena_dokumenta', function (Blueprint $table) {
            $table->dropIndex('kpd_kandidat_review_status_idx');
            $table->dropIndex('kpd_reviewer_id_idx');
            $table->dropColumn(['review_status', 'reviewer_id', 'notes', 'reviewed_at']);
        });
    }
};