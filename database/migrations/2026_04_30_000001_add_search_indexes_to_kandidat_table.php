<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add indexes on frequently searched/sorted columns in the kandidat table.
 *
 * Affected queries:
 *   - SearchController: WHERE imeKandidata LIKE '%q%' OR prezimeKandidata LIKE '%q%' OR jmbg LIKE '%q%'
 *   - KandidatService::searchKandidati(): WHERE imeKandidata LIKE '%q%'
 *   - PredictionController / DocumentReviewService: ORDER BY prezimeKandidata, imeKandidata
 *
 * Note: B-tree indexes on LIKE columns only help for prefix searches (LIKE 'x%').
 * Leading-wildcard LIKE ('%x%') still requires a full table scan, but ORDER BY
 * on indexed columns is significantly faster.
 *
 * A composite index (prezimeKandidata, imeKandidata) is added to cover the
 * common ORDER BY pair used throughout the codebase.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kandidat', function (Blueprint $table) {
            $table->index('imeKandidata', 'kandidat_imekandidata_index');
            $table->index('prezimeKandidata', 'kandidat_prezimekandidata_index');
            $table->index('jmbg', 'kandidat_jmbg_index');
            // Composite index to speed up ORDER BY prezimeKandidata, imeKandidata
            $table->index(['prezimeKandidata', 'imeKandidata'], 'kandidat_prezime_ime_index');
        });
    }

    public function down(): void
    {
        Schema::table('kandidat', function (Blueprint $table) {
            $table->dropIndex('kandidat_imekandidata_index');
            $table->dropIndex('kandidat_prezimekandidata_index');
            $table->dropIndex('kandidat_jmbg_index');
            $table->dropIndex('kandidat_prezime_ime_index');
        });
    }
};
