<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupOrphanedRecords extends Command
{
    protected $signature = 'cleanup:orphaned-records
                            {--dry-run : Show what would be deleted/fixed without making changes}
                            {--fix-nulls : Set NULL foreign keys to default values instead of deleting}';

    protected $description = 'Pre-migration cleanup: detects and fixes orphaned records before adding FK constraints (FAZA 2.5)';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $fixNulls = $this->option('fix-nulls');

        if ($dryRun) {
            $this->warn('DRY RUN MODE — no changes will be made.');
        }

        $this->info('=== FAZA 2.5: Čišćenje orphaned zapisa ===');
        $this->newLine();

        $totalIssues = 0;

        $totalIssues += $this->analyzeTable(
            'kandidat',
            [
                'studijskiProgram_id' => ['table' => 'studijski_program', 'nullable' => false],
                'tipStudija_id' => ['table' => 'tip_studija',       'nullable' => false],
                'skolskaGodinaUpisa_id' => ['table' => 'skolska_god_upisa', 'nullable' => true],
                'statusUpisa_id' => ['table' => 'status_studiranja', 'nullable' => true],
            ],
            $dryRun,
            $fixNulls
        );

        $totalIssues += $this->analyzeTable(
            'upis_godine',
            [
                'kandidat_id' => ['table' => 'kandidat',          'nullable' => false],
                'studijskiProgram_id' => ['table' => 'studijski_program', 'nullable' => false],
                'tipStudija_id' => ['table' => 'tip_studija',       'nullable' => false],
                'statusGodine_id' => ['table' => 'status_godine',     'nullable' => false],
            ],
            $dryRun,
            $fixNulls
        );

        $totalIssues += $this->analyzeTable(
            'prijava_ispita',
            [
                'kandidat_id' => ['table' => 'kandidat',               'nullable' => true],
                'predmet_id' => ['table' => 'predmet_program',        'nullable' => true],
                'profesor_id' => ['table' => 'profesor',               'nullable' => true],
                'rok_id' => ['table' => 'aktivni_ispitni_rokovi', 'nullable' => true],
            ],
            $dryRun,
            $fixNulls
        );

        $totalIssues += $this->analyzeTable(
            'polozeni_ispiti',
            [
                'kandidat_id' => ['table' => 'kandidat',                      'nullable' => true],
                'predmet_id' => ['table' => 'predmet_program',               'nullable' => true],
                'prijava_id' => ['table' => 'prijava_ispita',                'nullable' => true],
                'zapisnik_id' => ['table' => 'zapisnik_o_polaganju_ispita',   'nullable' => true],
            ],
            $dryRun,
            $fixNulls
        );

        $totalIssues += $this->analyzeTable(
            'zapisnik_o_polaganju_ispita',
            [
                'predmet_id' => ['table' => 'predmet',                'nullable' => true],
                'profesor_id' => ['table' => 'profesor',               'nullable' => true],
                'rok_id' => ['table' => 'aktivni_ispitni_rokovi', 'nullable' => true],
                'prijavaIspita_id' => ['table' => 'prijava_ispita',   'nullable' => true],
            ],
            $dryRun,
            $fixNulls
        );

        $totalIssues += $this->analyzeTable(
            'zapisnik_o_polaganju__student',
            [
                'zapisnik_id' => ['table' => 'zapisnik_o_polaganju_ispita', 'nullable' => false],
                'kandidat_id' => ['table' => 'kandidat',                    'nullable' => false],
                'prijavaIspita_id' => ['table' => 'prijava_ispita',         'nullable' => false],
            ],
            $dryRun,
            $fixNulls
        );

        $totalIssues += $this->analyzeTable(
            'zapisnik_o_polaganju__studijski_program',
            [
                'zapisnik_id' => ['table' => 'zapisnik_o_polaganju_ispita', 'nullable' => false],
                'StudijskiProgram_id' => ['table' => 'studijski_program',           'nullable' => false],
            ],
            $dryRun,
            $fixNulls
        );

        $this->newLine();

        if ($totalIssues === 0) {
            $this->info('✓ Nema orphaned zapisa. Baza je čista — možete pokrenuti migraciju.');
            $this->info('  Sledeći korak: php artisan migrate');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->error("Pronađeno {$totalIssues} problema. Pokrenite bez --dry-run da biste primenili popravke.");
            $this->line('  ili: php artisan cleanup:orphaned-records --fix-nulls (postavi NULL umesto brisanja)');
        } else {
            $this->warn("Obrađeno {$totalIssues} problema. Proverite izlaz i pokrenite php artisan migrate.");
        }

        return self::SUCCESS;
    }

    private function analyzeTable(string $table, array $foreignKeys, bool $dryRun, bool $fixNulls): int
    {
        $totalIssues = 0;

        foreach ($foreignKeys as $column => $meta) {
            $referencedTable = $meta['table'];
            $isNullable = $meta['nullable'];

            $orphanCount = DB::table($table)
                ->whereNotNull($column)
                ->whereNotIn($column, DB::table($referencedTable)->pluck('id'))
                ->count();

            $nullCount = DB::table($table)->whereNull($column)->count();

            if ($orphanCount > 0) {
                $totalIssues += $orphanCount;
                $this->warn("  [{$table}.{$column}] {$orphanCount} orphaned zapisa (ref. {$referencedTable})");

                if (! $dryRun) {
                    if ($isNullable && $fixNulls) {
                        DB::table($table)
                            ->whereNotNull($column)
                            ->whereNotIn($column, DB::table($referencedTable)->pluck('id'))
                            ->update([$column => null]);
                        $this->line("    → Postavljeno na NULL: {$orphanCount} zapisa");
                    } else {
                        $deleted = DB::table($table)
                            ->whereNotNull($column)
                            ->whereNotIn($column, DB::table($referencedTable)->pluck('id'))
                            ->delete();
                        $this->line("    → Obrisano: {$deleted} zapisa");
                    }
                }
            } else {
                $this->line("  <fg=green>✓</> [{$table}.{$column}] OK".($nullCount > 0 ? " ({$nullCount} NULL vrednosti)" : ''));
            }
        }

        return $totalIssues;
    }
}
