<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * FAZA 2.4.5: Popravljanje tipova kolona za Foreign Key kompatibilnost
 *
 * Problem: Laravel's increments() kreira UNSIGNED INT kolone, dok integer()
 * kreira SIGNED INT kolone. MySQL 8 ne dozvoljava foreign key constrainte
 * između kolona različitih sign-ova (greška 3780).
 *
 * Rešenje: Konvertuj sve foreign key kolone u UNSIGNED da odgovaraju
 * njihovim parent primary key kolonama.
 *
 * Analiza mismatch-ova:
 *
 * Parent tabele (sve koriste increments() = INT UNSIGNED):
 *   kandidat.id               → INT UNSIGNED
 *   studijski_program.id      → INT UNSIGNED
 *   tip_studija.id            → INT UNSIGNED
 *   skolska_god_upisa.id      → INT UNSIGNED
 *   status_studiranja.id      → INT UNSIGNED
 *   predmet.id                → INT UNSIGNED
 *   profesor.id               → INT UNSIGNED
 *   aktivni_ispitni_rokovi.id → INT UNSIGNED
 *   prijava_ispita.id         → INT UNSIGNED
 *   zapisnik_o_polaganju_ispita.id → INT UNSIGNED
 *   status_godine.id          → BIGINT UNSIGNED (koristi $table->id())
 *
 * NAPOMENA: kandidat tabela ima kolone koje su već UNSIGNED (integer()->unsigned()
 * u originalnoj migraciji), pa te kolone NE treba menjati.
 *
 * Tabele sa SIGNED INT kolonama koje referenciraju UNSIGNED INT parent PK:
 *
 * MISMATCH: upis_godine.kandidat_id (SIGNED INT) → kandidat.id (UNSIGNED INT)
 * MISMATCH: upis_godine.studijskiProgram_id (SIGNED INT) → studijski_program.id (UNSIGNED INT)
 * MISMATCH: upis_godine.tipStudija_id (SIGNED INT) → tip_studija.id (UNSIGNED INT)
 * MISMATCH: upis_godine.statusGodine_id (SIGNED INT) → status_godine.id (BIGINT UNSIGNED)
 *
 * MISMATCH: prijava_ispita.kandidat_id (SIGNED INT, nullable) → kandidat.id (UNSIGNED INT)
 * MISMATCH: prijava_ispita.predmet_id (SIGNED INT, nullable) → predmet.id (UNSIGNED INT)
 * MISMATCH: prijava_ispita.profesor_id (SIGNED INT, nullable) → profesor.id (UNSIGNED INT)
 * MISMATCH: prijava_ispita.rok_id (SIGNED INT, nullable) → aktivni_ispitni_rokovi.id (UNSIGNED INT)
 *
 * MISMATCH: polozeni_ispiti.kandidat_id (SIGNED INT, nullable) → kandidat.id (UNSIGNED INT)
 * MISMATCH: polozeni_ispiti.predmet_id (SIGNED INT, nullable) → predmet.id (UNSIGNED INT)
 * MISMATCH: polozeni_ispiti.prijava_id (SIGNED INT, nullable) → prijava_ispita.id (UNSIGNED INT)
 * MISMATCH: polozeni_ispiti.zapisnik_id (SIGNED INT, nullable) → zapisnik_o_polaganju_ispita.id (UNSIGNED INT)
 *
 * MISMATCH: zapisnik_o_polaganju_ispita.predmet_id (SIGNED INT) → predmet.id (UNSIGNED INT)
 * MISMATCH: zapisnik_o_polaganju_ispita.profesor_id (SIGNED INT) → profesor.id (UNSIGNED INT)
 * MISMATCH: zapisnik_o_polaganju_ispita.rok_id (SIGNED INT) → aktivni_ispitni_rokovi.id (UNSIGNED INT)
 *
 * MISMATCH: zapisnik_o_polaganju__student.zapisnik_id (SIGNED INT) → zapisnik_o_polaganju_ispita.id (UNSIGNED INT)
 * MISMATCH: zapisnik_o_polaganju__student.kandidat_id (SIGNED INT) → kandidat.id (UNSIGNED INT)
 *
 * MISMATCH: zapisnik_o_polaganju__studijski_program.zapisnik_id (SIGNED INT) → zapisnik_o_polaganju_ispita.id (UNSIGNED INT)
 * MISMATCH: zapisnik_o_polaganju__studijski_program.StudijskiProgram_id (SIGNED INT) → studijski_program.id (UNSIGNED INT)
 */
return new class extends Migration
{
    /**
     * Konvertuj sve FK kolone u UNSIGNED da odgovaraju parent PK kolonama.
     * Ova migracija mora biti pokrenuta PRE 2026_04_01_102524_add_foreign_key_constraints_to_core_tables.php
     */
    public function up(): void
    {
        // ----------------------------------------------------------------
        // 1. UPIS_GODINE
        // Sve četiri FK kolone su SIGNED INT, moraju biti UNSIGNED.
        // statusGodine_id mora biti BIGINT UNSIGNED jer status_godine.id
        // koristi $table->id() = BIGINT UNSIGNED.
        // ----------------------------------------------------------------
        // Fix: kandidat_id mora biti UNSIGNED da odgovara kandidat.id (increments = INT UNSIGNED)
        DB::statement('ALTER TABLE upis_godine MODIFY COLUMN kandidat_id INT UNSIGNED NOT NULL');

        // Fix: studijskiProgram_id mora biti UNSIGNED da odgovara studijski_program.id (increments = INT UNSIGNED)
        DB::statement('ALTER TABLE upis_godine MODIFY COLUMN studijskiProgram_id INT UNSIGNED NOT NULL');

        // Fix: tipStudija_id mora biti UNSIGNED da odgovara tip_studija.id (increments = INT UNSIGNED)
        DB::statement('ALTER TABLE upis_godine MODIFY COLUMN tipStudija_id INT UNSIGNED NOT NULL');

        // Fix: statusGodine_id mora biti BIGINT UNSIGNED da odgovara status_godine.id ($table->id() = BIGINT UNSIGNED)
        DB::statement('ALTER TABLE upis_godine MODIFY COLUMN statusGodine_id BIGINT UNSIGNED NOT NULL');

        // ----------------------------------------------------------------
        // 2. PRIJAVA_ISPITA
        // Sve četiri FK kolone su SIGNED INT i nullable
        // (postavljene na nullable u fix_all_nullable_columns migraciji).
        // ----------------------------------------------------------------
        // Fix: kandidat_id mora biti UNSIGNED da odgovara kandidat.id (increments = INT UNSIGNED)
        DB::statement('ALTER TABLE prijava_ispita MODIFY COLUMN kandidat_id INT UNSIGNED NULL');

        // Fix: predmet_id mora biti UNSIGNED da odgovara predmet.id (increments = INT UNSIGNED)
        DB::statement('ALTER TABLE prijava_ispita MODIFY COLUMN predmet_id INT UNSIGNED NULL');

        // Fix: profesor_id mora biti UNSIGNED da odgovara profesor.id (increments = INT UNSIGNED)
        DB::statement('ALTER TABLE prijava_ispita MODIFY COLUMN profesor_id INT UNSIGNED NULL');

        // Fix: rok_id mora biti UNSIGNED da odgovara aktivni_ispitni_rokovi.id (increments = INT UNSIGNED)
        DB::statement('ALTER TABLE prijava_ispita MODIFY COLUMN rok_id INT UNSIGNED NULL');

        // ----------------------------------------------------------------
        // 3. POLOZENI_ISPITI
        // Sve četiri FK kolone su SIGNED INT i nullable
        // (postavljene na nullable u fix_all_nullable_columns migraciji).
        // ----------------------------------------------------------------
        // Fix: kandidat_id mora biti UNSIGNED da odgovara kandidat.id (increments = INT UNSIGNED)
        DB::statement('ALTER TABLE polozeni_ispiti MODIFY COLUMN kandidat_id INT UNSIGNED NULL');

        // Fix: predmet_id mora biti UNSIGNED da odgovara predmet.id (increments = INT UNSIGNED)
        DB::statement('ALTER TABLE polozeni_ispiti MODIFY COLUMN predmet_id INT UNSIGNED NULL');

        // Fix: prijava_id mora biti UNSIGNED da odgovara prijava_ispita.id (increments = INT UNSIGNED)
        DB::statement('ALTER TABLE polozeni_ispiti MODIFY COLUMN prijava_id INT UNSIGNED NULL');

        // Fix: zapisnik_id mora biti UNSIGNED da odgovara zapisnik_o_polaganju_ispita.id (increments = INT UNSIGNED)
        DB::statement('ALTER TABLE polozeni_ispiti MODIFY COLUMN zapisnik_id INT UNSIGNED NULL');

        // ----------------------------------------------------------------
        // 4. ZAPISNIK_O_POLAGANJU_ISPITA
        // Tri FK kolone su SIGNED INT. predmet_id i profesor_id su NOT NULL
        // u originalnoj migraciji; rok_id je nullable u make_zapisnik_columns_nullable.
        // ----------------------------------------------------------------
        // Fix: predmet_id mora biti UNSIGNED da odgovara predmet.id (increments = INT UNSIGNED)
        DB::statement('ALTER TABLE zapisnik_o_polaganju_ispita MODIFY COLUMN predmet_id INT UNSIGNED NULL');

        // Fix: profesor_id mora biti UNSIGNED da odgovara profesor.id (increments = INT UNSIGNED)
        DB::statement('ALTER TABLE zapisnik_o_polaganju_ispita MODIFY COLUMN profesor_id INT UNSIGNED NULL');

        // Fix: rok_id mora biti UNSIGNED da odgovara aktivni_ispitni_rokovi.id (increments = INT UNSIGNED)
        DB::statement('ALTER TABLE zapisnik_o_polaganju_ispita MODIFY COLUMN rok_id INT UNSIGNED NULL');

        // ----------------------------------------------------------------
        // 5. ZAPISNIK_O_POLAGANJU__STUDENT (pivot tabela)
        // Obe FK kolone su SIGNED INT i NOT NULL.
        // ----------------------------------------------------------------
        // Fix: zapisnik_id mora biti UNSIGNED da odgovara zapisnik_o_polaganju_ispita.id (increments = INT UNSIGNED)
        DB::statement('ALTER TABLE zapisnik_o_polaganju__student MODIFY COLUMN zapisnik_id INT UNSIGNED NOT NULL');

        // Fix: kandidat_id mora biti UNSIGNED da odgovara kandidat.id (increments = INT UNSIGNED)
        DB::statement('ALTER TABLE zapisnik_o_polaganju__student MODIFY COLUMN kandidat_id INT UNSIGNED NOT NULL');

        // ----------------------------------------------------------------
        // 6. ZAPISNIK_O_POLAGANJU__STUDIJSKI_PROGRAM (pivot tabela)
        // Obe FK kolone su SIGNED INT i NOT NULL.
        // NAPOMENA: Kolona se zove 'StudijskiProgram_id' (veliko S).
        // ----------------------------------------------------------------
        // Fix: zapisnik_id mora biti UNSIGNED da odgovara zapisnik_o_polaganju_ispita.id (increments = INT UNSIGNED)
        DB::statement('ALTER TABLE zapisnik_o_polaganju__studijski_program MODIFY COLUMN zapisnik_id INT UNSIGNED NOT NULL');

        // Fix: StudijskiProgram_id mora biti UNSIGNED da odgovara studijski_program.id (increments = INT UNSIGNED)
        DB::statement('ALTER TABLE zapisnik_o_polaganju__studijski_program MODIFY COLUMN StudijskiProgram_id INT UNSIGNED NOT NULL');
    }

    /**
     * Obrnuti sve izmene — vrati kolone na originalni SIGNED tip.
     */
    public function down(): void
    {
        // Obrnuto od up() — vrati na SIGNED INT

        // zapisnik_o_polaganju__studijski_program
        DB::statement('ALTER TABLE zapisnik_o_polaganju__studijski_program MODIFY COLUMN zapisnik_id INT NOT NULL');
        DB::statement('ALTER TABLE zapisnik_o_polaganju__studijski_program MODIFY COLUMN StudijskiProgram_id INT NOT NULL');

        // zapisnik_o_polaganju__student
        DB::statement('ALTER TABLE zapisnik_o_polaganju__student MODIFY COLUMN zapisnik_id INT NOT NULL');
        DB::statement('ALTER TABLE zapisnik_o_polaganju__student MODIFY COLUMN kandidat_id INT NOT NULL');

        // zapisnik_o_polaganju_ispita
        DB::statement('ALTER TABLE zapisnik_o_polaganju_ispita MODIFY COLUMN predmet_id INT NULL');
        DB::statement('ALTER TABLE zapisnik_o_polaganju_ispita MODIFY COLUMN profesor_id INT NULL');
        DB::statement('ALTER TABLE zapisnik_o_polaganju_ispita MODIFY COLUMN rok_id INT NULL');

        // polozeni_ispiti
        DB::statement('ALTER TABLE polozeni_ispiti MODIFY COLUMN kandidat_id INT NULL');
        DB::statement('ALTER TABLE polozeni_ispiti MODIFY COLUMN predmet_id INT NULL');
        DB::statement('ALTER TABLE polozeni_ispiti MODIFY COLUMN prijava_id INT NULL');
        DB::statement('ALTER TABLE polozeni_ispiti MODIFY COLUMN zapisnik_id INT NULL');

        // prijava_ispita
        DB::statement('ALTER TABLE prijava_ispita MODIFY COLUMN kandidat_id INT NULL');
        DB::statement('ALTER TABLE prijava_ispita MODIFY COLUMN predmet_id INT NULL');
        DB::statement('ALTER TABLE prijava_ispita MODIFY COLUMN profesor_id INT NULL');
        DB::statement('ALTER TABLE prijava_ispita MODIFY COLUMN rok_id INT NULL');

        // upis_godine
        DB::statement('ALTER TABLE upis_godine MODIFY COLUMN kandidat_id INT NOT NULL');
        DB::statement('ALTER TABLE upis_godine MODIFY COLUMN studijskiProgram_id INT NOT NULL');
        DB::statement('ALTER TABLE upis_godine MODIFY COLUMN tipStudija_id INT NOT NULL');
        DB::statement('ALTER TABLE upis_godine MODIFY COLUMN statusGodine_id INT NOT NULL');
    }
};
