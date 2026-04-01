<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FAZA 2.5: Dodavanje Foreign Key Constraints
 *
 * Ova migracija dodaje foreign key constrainte na sve ključne tabele
 * kako bi se osigurao integritet podataka i spriječili orphaned zapisi.
 *
 * VAŽNO: Pre pokretanja ove migracije, pokrenite:
 *   php artisan cleanup:orphaned-records
 *
 * Strategija onDelete:
 * - CASCADE:   Za zavisne zapise (npr. upis_godine -> kandidat)
 *              Ako se roditeljelski zapis obriše, brišu se i svi zavisni.
 * - RESTRICT:  Za lookup tabele (npr. kandidat -> studijskiProgram)
 *              Sprečava brisanje roditeljevskog zapisa ako postoje zavisni.
 * - SET NULL:  Za opcionalne reference (npr. polozeni_ispiti -> zapisnik)
 *              Ako se zapisnik obriše, polje se postavlja na NULL (nullable kolona).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds foreign key constraints to 7 core tables in dependency order:
     * 1. kandidat (references lookup tables)
     * 2. upis_godine (references kandidat + lookup tables)
     * 3. prijava_ispita (references kandidat + lookup tables)
     * 4. polozeni_ispiti (references kandidat, prijava_ispita, zapisnik)
     * 5. zapisnik_o_polaganju_ispita (references lookup tables)
     * 6. zapisnik_o_polaganju__student (references zapisnik + kandidat)
     * 7. zapisnik_o_polaganju__studijski_program (references zapisnik + studijskiProgram)
     */
    public function up(): void
    {
        // ----------------------------------------------------------------
        // 1. KANDIDAT
        // Referira na lookup tabele — koristimo RESTRICT da spriječimo
        // brisanje programa/tipa/statusa dok postoje kandidati koji ih koriste.
        // studijskiProgram_id, tipStudija_id su NOT NULL u originalnoj migraciji.
        // statusUpisa_id, skolskaGodinaUpisa_id su nullable u originalnoj migraciji.
        // ----------------------------------------------------------------
        Schema::table('kandidat', function (Blueprint $table) {
            // Ne dozvoliti brisanje studijskog programa koji ima kandidate
            $table->foreign('studijskiProgram_id')
                ->references('id')->on('studijski_program')
                ->onDelete('restrict');

            // Ne dozvoliti brisanje tipa studija koji ima kandidate
            $table->foreign('tipStudija_id')
                ->references('id')->on('tip_studija')
                ->onDelete('restrict');

            // Ne dozvoliti brisanje školske godine upisa koja ima kandidate
            // (nullable kolona — safe za FK bez promene nullable-a)
            $table->foreign('skolskaGodinaUpisa_id')
                ->references('id')->on('skolska_god_upisa')
                ->onDelete('restrict');

            // Ne dozvoliti brisanje statusa upisa koji se koristi (nullable kolona)
            $table->foreign('statusUpisa_id')
                ->references('id')->on('status_studiranja')
                ->onDelete('restrict');
        });

        // ----------------------------------------------------------------
        // 2. UPIS_GODINE
        // Referira na kandidat (CASCADE) i lookup tabele (RESTRICT).
        // Brisanjem kandidata automatski se brišu svi njegovi upisi.
        // ----------------------------------------------------------------
        Schema::table('upis_godine', function (Blueprint $table) {
            // Kaskadno brisanje: ako se kandidat obriše, brišu se i svi njegovi upisi
            $table->foreign('kandidat_id')
                ->references('id')->on('kandidat')
                ->onDelete('cascade');

            // Ne dozvoliti brisanje studijskog programa koji ima upisane kandidate
            $table->foreign('studijskiProgram_id')
                ->references('id')->on('studijski_program')
                ->onDelete('restrict');

            // Ne dozvoliti brisanje tipa studija koji se koristi u upisima
            $table->foreign('tipStudija_id')
                ->references('id')->on('tip_studija')
                ->onDelete('restrict');

            // Ne dozvoliti brisanje statusa godine dok postoje upisi sa tim statusom
            $table->foreign('statusGodine_id')
                ->references('id')->on('status_godine')
                ->onDelete('restrict');
        });

        // ----------------------------------------------------------------
        // 3. PRIJAVA_ISPITA
        // Referira na kandidat (CASCADE), predmet, profesor, rok (RESTRICT).
        // Sve kolone su nullable (fix_all_nullable_columns migracija).
        // Brisanjem kandidata kaskadno se brišu sve njegove prijave ispita.
        // ----------------------------------------------------------------
        Schema::table('prijava_ispita', function (Blueprint $table) {
            // Kaskadno brisanje: ako se kandidat obriše, brišu se i sve njegove prijave
            $table->foreign('kandidat_id')
                ->references('id')->on('kandidat')
                ->onDelete('cascade');

            // Ne dozvoliti brisanje predmeta koji ima aktivne prijave
            $table->foreign('predmet_id')
                ->references('id')->on('predmet')
                ->onDelete('restrict');

            // Ne dozvoliti brisanje profesora koji ima prijave ispita
            $table->foreign('profesor_id')
                ->references('id')->on('profesor')
                ->onDelete('restrict');

            // Ne dozvoliti brisanje ispitnog roka koji ima prijave
            $table->foreign('rok_id')
                ->references('id')->on('aktivni_ispitni_rokovi')
                ->onDelete('restrict');
        });

        // ----------------------------------------------------------------
        // 4. POLOZENI_ISPITI
        // Referira na kandidat (CASCADE), predmet (RESTRICT),
        // prijava_ispita (CASCADE), zapisnik (SET NULL).
        //
        // zapisnik_id koristi SET NULL jer je kolona nullable —
        // dozvoljavamo brisanje zapisnika bez gubitka informacije o položenom ispitu.
        // prijava_id: CASCADE jer polozeni_ispit bez prijave nema smisla.
        // Sve kolone su nullable (fix_all_nullable_columns migracija).
        // ----------------------------------------------------------------
        Schema::table('polozeni_ispiti', function (Blueprint $table) {
            // Kaskadno brisanje: ako se kandidat obriše, brišu se i svi položeni ispiti
            $table->foreign('kandidat_id')
                ->references('id')->on('kandidat')
                ->onDelete('cascade');

            // Ne dozvoliti brisanje predmeta koji ima položene ispite
            $table->foreign('predmet_id')
                ->references('id')->on('predmet')
                ->onDelete('restrict');

            // Kaskadno brisanje: ako se prijava obriše, briše se i rezultat
            $table->foreign('prijava_id')
                ->references('id')->on('prijava_ispita')
                ->onDelete('cascade');

            // SET NULL: Dozvoliti brisanje zapisnika — kolona je nullable
            // Čuva evidenciju položenog ispita čak i ako zapisnik bude obrisan
            $table->foreign('zapisnik_id')
                ->references('id')->on('zapisnik_o_polaganju_ispita')
                ->onDelete('set null');
        });

        // ----------------------------------------------------------------
        // 5. ZAPISNIK_O_POLAGANJU_ISPITA
        // Referira na predmet, profesor (RESTRICT), rok (RESTRICT).
        // Sve ove kolone su nullable (make_zapisnik_columns_nullable migracija).
        // ----------------------------------------------------------------
        Schema::table('zapisnik_o_polaganju_ispita', function (Blueprint $table) {
            // Ne dozvoliti brisanje predmeta dok postoji zapisnik o polaganju
            $table->foreign('predmet_id')
                ->references('id')->on('predmet')
                ->onDelete('restrict');

            // Ne dozvoliti brisanje profesora dok postoji zapisnik o polaganju
            $table->foreign('profesor_id')
                ->references('id')->on('profesor')
                ->onDelete('restrict');

            // Ne dozvoliti brisanje ispitnog roka dok postoji zapisnik o polaganju
            $table->foreign('rok_id')
                ->references('id')->on('aktivni_ispitni_rokovi')
                ->onDelete('restrict');
        });

        // ----------------------------------------------------------------
        // 6. ZAPISNIK_O_POLAGANJU__STUDENT (pivot tabela)
        // Referira na zapisnik i kandidat — oba CASCADE.
        // Ako se zapisnik ili kandidat obriše, brišu se i pivot zapisi.
        // ----------------------------------------------------------------
        Schema::table('zapisnik_o_polaganju__student', function (Blueprint $table) {
            // Kaskadno brisanje: ako se zapisnik obriše, briše se i veza sa studentom
            $table->foreign('zapisnik_id')
                ->references('id')->on('zapisnik_o_polaganju_ispita')
                ->onDelete('cascade');

            // Kaskadno brisanje: ako se kandidat obriše, briše se i veza sa zapisnikom
            $table->foreign('kandidat_id')
                ->references('id')->on('kandidat')
                ->onDelete('cascade');
        });

        // ----------------------------------------------------------------
        // 7. ZAPISNIK_O_POLAGANJU__STUDIJSKI_PROGRAM (pivot tabela)
        // Referira na zapisnik i studijskiProgram — oba CASCADE.
        //
        // NAPOMENA: Kolona u bazi se zove 'StudijskiProgram_id' (veliki S)
        // dok spec koristi 'studijskiProgram_id' (mali s). Koristimo
        // stvarno ime kolone iz originalne migracije (2016_08_24_164537).
        // ----------------------------------------------------------------
        Schema::table('zapisnik_o_polaganju__studijski_program', function (Blueprint $table) {
            // Kaskadno brisanje: ako se zapisnik obriše, briše se i veza sa programom
            $table->foreign('zapisnik_id', 'zop_studprogram_zapisnik_fk')
                ->references('id')->on('zapisnik_o_polaganju_ispita')
                ->onDelete('cascade');

            // Kaskadno brisanje: ako se studijski program obriše, briše se i veza
            // Kolona se zove StudijskiProgram_id (veliko S) u originalnoj migraciji
            // Custom ime constrainta jer auto-generisano prekoračuje MySQL limit od 64 karaktera
            $table->foreign('StudijskiProgram_id', 'zop_studprogram_program_fk')
                ->references('id')->on('studijski_program')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Drops foreign key constraints in REVERSE order to avoid
     * dependency conflicts during rollback.
     */
    public function down(): void
    {
        // Drop u obrnutom redosledu od up() metode

        Schema::table('zapisnik_o_polaganju__studijski_program', function (Blueprint $table) {
            $table->dropForeign('zop_studprogram_zapisnik_fk');
            $table->dropForeign('zop_studprogram_program_fk');
        });

        Schema::table('zapisnik_o_polaganju__student', function (Blueprint $table) {
            $table->dropForeign(['zapisnik_id']);
            $table->dropForeign(['kandidat_id']);
        });

        Schema::table('zapisnik_o_polaganju_ispita', function (Blueprint $table) {
            $table->dropForeign(['predmet_id']);
            $table->dropForeign(['profesor_id']);
            $table->dropForeign(['rok_id']);
        });

        Schema::table('polozeni_ispiti', function (Blueprint $table) {
            $table->dropForeign(['kandidat_id']);
            $table->dropForeign(['predmet_id']);
            $table->dropForeign(['prijava_id']);
            $table->dropForeign(['zapisnik_id']);
        });

        Schema::table('prijava_ispita', function (Blueprint $table) {
            $table->dropForeign(['kandidat_id']);
            $table->dropForeign(['predmet_id']);
            $table->dropForeign(['profesor_id']);
            $table->dropForeign(['rok_id']);
        });

        Schema::table('upis_godine', function (Blueprint $table) {
            $table->dropForeign(['kandidat_id']);
            $table->dropForeign(['studijskiProgram_id']);
            $table->dropForeign(['tipStudija_id']);
            $table->dropForeign(['statusGodine_id']);
        });

        Schema::table('kandidat', function (Blueprint $table) {
            $table->dropForeign(['studijskiProgram_id']);
            $table->dropForeign(['tipStudija_id']);
            $table->dropForeign(['skolskaGodinaUpisa_id']);
            $table->dropForeign(['statusUpisa_id']);
        });
    }
};
