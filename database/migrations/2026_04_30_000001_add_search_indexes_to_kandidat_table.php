<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Add indexes on frequently searched/sorted columns in the kandidat table.
 *
 * Affected queries:
 *   - SearchController: WHERE imeKandidata LIKE '%q%' OR prezimeKandidata LIKE '%q%'
 *   - KandidatService::searchKandidati(): WHERE imeKandidata LIKE '%q%'
 *   - PredictionController / DocumentReviewService: ORDER BY prezimeKandidata, imeKandidata
 *
 * Note: B-tree indexes on LIKE columns only help for prefix searches (LIKE 'x%').
 * Leading-wildcard LIKE ('%x%') still requires a full table scan, but ORDER BY
 * on indexed columns is significantly faster.
 *
 * Explicit prefix lengths (100 chars) are used to stay within MySQL's key length
 * limit on utf8/utf8mb3 columns (100 * 3 = 300 bytes per column).
 *
 * jmbg is intentionally omitted — it already has UNIQUE KEY kandidat_jmbg_unique
 * on the full column, which covers all query patterns.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE INDEX kandidat_imekandidata_index ON kandidat (imeKandidata(100))');
        DB::statement('CREATE INDEX kandidat_prezimekandidata_index ON kandidat (prezimeKandidata(100))');
        // Composite index to speed up ORDER BY prezimeKandidata, imeKandidata
        DB::statement('CREATE INDEX kandidat_prezime_ime_index ON kandidat (prezimeKandidata(100), imeKandidata(100))');
    }

    public function down(): void
    {
        Schema::table('kandidat', function (Blueprint $table) {
            $table->dropIndex('kandidat_imekandidata_index');
            $table->dropIndex('kandidat_prezimekandidata_index');
            $table->dropIndex('kandidat_prezime_ime_index');
        });
    }
};
