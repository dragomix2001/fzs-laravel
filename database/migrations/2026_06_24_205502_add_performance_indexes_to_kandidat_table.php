<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds composite indexes to optimize the most frequent query patterns:
     *
     * 1. statusUpisa_id - Heavily used for filtering students (status 1=enrolled, 3=candidate)
     * 2. email - Used for API authentication (StudentController, AktivnostController)
     * 3. Composite (tipStudija_id, statusUpisa_id, studijskiProgram_id) - Most common filtering pattern
     * 4. Composite (tipStudija_id, statusUpisa_id, godinaStudija_id, studijskiProgram_id) - StudentController pattern
     *
     * Note: Individual indexes already exist for tipStudija_id, studijskiProgram_id, and godinaStudija_id.
     * The composite indexes will be used by MySQL query optimizer when filtering by multiple columns.
     */
    public function up(): void
    {
        Schema::table('kandidat', function (Blueprint $table) {
            // Single column index for statusUpisa_id (heavily filtered: enrolled, candidate, frozen)
            $table->index('statusUpisa_id', 'kandidat_statusupisa_id_index');

            // Index for API authentication by email
            $table->index('email', 'kandidat_email_index');

            // Composite index for most common filter pattern (KandidatService::getAll, PrijavaController)
            $table->index(['tipStudija_id', 'statusUpisa_id', 'studijskiProgram_id'], 'kandidat_tip_status_program_index');

            // Composite index for StudentController pattern (includes godinaStudija_id)
            $table->index(['tipStudija_id', 'statusUpisa_id', 'godinaStudija_id', 'studijskiProgram_id'], 'kandidat_tip_status_godina_program_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kandidat', function (Blueprint $table) {
            $table->dropIndex('kandidat_statusupisa_id_index');
            $table->dropIndex('kandidat_email_index');
            $table->dropIndex('kandidat_tip_status_program_index');
            $table->dropIndex('kandidat_tip_status_godina_program_index');
        });
    }
};
