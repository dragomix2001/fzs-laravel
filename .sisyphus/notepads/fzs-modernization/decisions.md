# FAZA 2.5: Foreign Key Constraints — Architectural Decisions

## Date: 2026-04-01

### Decision 1: Column name discrepancy — StudijskiProgram_id (capital S)

The original migration `2016_08_24_164537_create_zapisnik_o_polaganju__studijski_program_table.php`
defines the column as `StudijskiProgram_id` (capital S). The README_IMPROVEMENTS.md spec uses
`studijskiProgram_id` (lowercase s). We use the ACTUAL column name from the DB (`StudijskiProgram_id`)
in both the migration and the cleanup command. This matters because MySQL on case-sensitive filesystems
will treat these as different column names.

### Decision 2: onDelete strategies

| Table | Column | Strategy | Rationale |
|-------|--------|----------|-----------|
| kandidat | studijskiProgram_id | RESTRICT | Lookup table — program should not be deletable if candidates exist |
| kandidat | tipStudija_id | RESTRICT | Lookup table |
| kandidat | skolskaGodinaUpisa_id | RESTRICT | Lookup table (nullable col, safe for FK) |
| kandidat | statusUpisa_id | RESTRICT | Lookup table (nullable col) |
| upis_godine | kandidat_id | CASCADE | upis has no meaning without kandidat |
| upis_godine | studijskiProgram_id | RESTRICT | Lookup table |
| upis_godine | tipStudija_id | RESTRICT | Lookup table |
| upis_godine | statusGodine_id | RESTRICT | Lookup table |
| prijava_ispita | kandidat_id | CASCADE | prijava has no meaning without kandidat |
| prijava_ispita | predmet_id | RESTRICT | Lookup table |
| prijava_ispita | profesor_id | RESTRICT | Lookup table |
| prijava_ispita | rok_id | RESTRICT | Lookup table |
| polozeni_ispiti | kandidat_id | CASCADE | Exam result tied to student |
| polozeni_ispiti | predmet_id | RESTRICT | Lookup table |
| polozeni_ispiti | prijava_id | CASCADE | Result has no meaning without the application |
| polozeni_ispiti | zapisnik_id | SET NULL | zapisnik_id is nullable — preserve exam history if zapisnik deleted |
| zapisnik_o_polaganju_ispita | predmet_id | RESTRICT | Lookup table (nullable col) |
| zapisnik_o_polaganju_ispita | profesor_id | RESTRICT | Lookup table (nullable col) |
| zapisnik_o_polaganju_ispita | rok_id | RESTRICT | Lookup table (nullable col) |
| zapisnik_o_polaganju__student | zapisnik_id | CASCADE | Pivot row has no meaning without zapisnik |
| zapisnik_o_polaganju__student | kandidat_id | CASCADE | Pivot row has no meaning without kanditat |
| zapisnik_o_polaganju__studijski_program | zapisnik_id | CASCADE | Pivot row tied to zapisnik |
| zapisnik_o_polaganju__studijski_program | StudijskiProgram_id | CASCADE | Pivot row tied to program |

### Decision 3: Nullable columns and FKs

Several columns were made nullable in earlier 2026 migrations (make_zapisnik_columns_nullable,
fix_all_nullable_columns). FKs on nullable columns are valid in MySQL — NULL values are allowed
and skip the FK check. This means prijava_ispita.kandidat_id = NULL passes FK validation even
though the column has a FK to kandidat.id. This is intentional to preserve existing data.

### Decision 4: Kernel.php registration

The project uses old-style bootstrap (explicit App\Console\Kernel) not Laravel 13's new
`withCommands()` auto-discovery via bootstrap/app.php. CleanupOrphanedRecords was added to
`$commands` array in Kernel.php to ensure it's registered.

### Decision 5: CleanupOrphanedRecords command signature

Chose `cleanup:orphaned-records` (per spec line 232) with:
- `--dry-run`: Safe inspection mode (recommended first step)
- `--fix-nulls`: For nullable FKs, set to NULL instead of deleting

### Decision 6: zapisnik_o_polaganju_ispita has no kandidat_id FK

The spec does NOT list kandidat_id as a FK for zapisnik_o_polaganju_ispita even though
the column exists. This is correct — that column was made nullable (migration 2026_03_11_000001)
and is not a FK per spec. We follow the spec exactly.
