<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $result = DB::select(
            "SELECT COUNT(*) as cnt FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ?
               AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
            [$table, $constraintName]
        );

        return $result[0]->cnt > 0;
    }

    private function dropForeignIfExists(string $table, string $constraintName): void
    {
        if (! $this->foreignKeyExists($table, $constraintName)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($constraintName) {
            $tableBlueprint->dropForeign($constraintName);
        });
    }

    public function up(): void
    {
        Schema::table('kandidat', function (Blueprint $table) {
            if (! $this->foreignKeyExists('kandidat', 'kandidat_studijskiprogram_id_foreign')) {
                $table->foreign('studijskiProgram_id')
                    ->references('id')->on('studijski_program')
                    ->onDelete('restrict');
            }
            if (! $this->foreignKeyExists('kandidat', 'kandidat_tipstudija_id_foreign')) {
                $table->foreign('tipStudija_id')
                    ->references('id')->on('tip_studija')
                    ->onDelete('restrict');
            }
            if (! $this->foreignKeyExists('kandidat', 'kandidat_skolskagodinaupisa_id_foreign')) {
                $table->foreign('skolskaGodinaUpisa_id')
                    ->references('id')->on('skolska_god_upisa')
                    ->onDelete('restrict');
            }
            if (! $this->foreignKeyExists('kandidat', 'kandidat_statusupisa_id_foreign')) {
                $table->foreign('statusUpisa_id')
                    ->references('id')->on('status_studiranja')
                    ->onDelete('restrict');
            }
        });

        Schema::table('upis_godine', function (Blueprint $table) {
            if (! $this->foreignKeyExists('upis_godine', 'upis_godine_kandidat_id_foreign')) {
                $table->foreign('kandidat_id')
                    ->references('id')->on('kandidat')
                    ->onDelete('cascade');
            }
            if (! $this->foreignKeyExists('upis_godine', 'upis_godine_studijskiprogram_id_foreign')) {
                $table->foreign('studijskiProgram_id')
                    ->references('id')->on('studijski_program')
                    ->onDelete('restrict');
            }
            if (! $this->foreignKeyExists('upis_godine', 'upis_godine_tipstudija_id_foreign')) {
                $table->foreign('tipStudija_id')
                    ->references('id')->on('tip_studija')
                    ->onDelete('restrict');
            }
            if (! $this->foreignKeyExists('upis_godine', 'upis_godine_statusgodine_id_foreign')) {
                $table->foreign('statusGodine_id')
                    ->references('id')->on('status_godine')
                    ->onDelete('restrict');
            }
        });

        Schema::table('prijava_ispita', function (Blueprint $table) {
            if (! $this->foreignKeyExists('prijava_ispita', 'prijava_ispita_kandidat_id_foreign')) {
                $table->foreign('kandidat_id')
                    ->references('id')->on('kandidat')
                    ->onDelete('cascade');
            }
            if (! $this->foreignKeyExists('prijava_ispita', 'prijava_ispita_predmet_id_foreign')) {
                $table->foreign('predmet_id')
                    ->references('id')->on('predmet_program')
                    ->onDelete('restrict');
            }
            if (! $this->foreignKeyExists('prijava_ispita', 'prijava_ispita_profesor_id_foreign')) {
                $table->foreign('profesor_id')
                    ->references('id')->on('profesor')
                    ->onDelete('restrict');
            }
            if (! $this->foreignKeyExists('prijava_ispita', 'prijava_ispita_rok_id_foreign')) {
                $table->foreign('rok_id')
                    ->references('id')->on('aktivni_ispitni_rokovi')
                    ->onDelete('restrict');
            }
        });

        Schema::table('polozeni_ispiti', function (Blueprint $table) {
            if (! $this->foreignKeyExists('polozeni_ispiti', 'polozeni_ispiti_kandidat_id_foreign')) {
                $table->foreign('kandidat_id')
                    ->references('id')->on('kandidat')
                    ->onDelete('cascade');
            }
            if (! $this->foreignKeyExists('polozeni_ispiti', 'polozeni_ispiti_predmet_id_foreign')) {
                $table->foreign('predmet_id')
                    ->references('id')->on('predmet_program')
                    ->onDelete('restrict');
            }
            if (! $this->foreignKeyExists('polozeni_ispiti', 'polozeni_ispiti_prijava_id_foreign')) {
                $table->foreign('prijava_id')
                    ->references('id')->on('prijava_ispita')
                    ->onDelete('cascade');
            }
            if (! $this->foreignKeyExists('polozeni_ispiti', 'polozeni_ispiti_zapisnik_id_foreign')) {
                $table->foreign('zapisnik_id')
                    ->references('id')->on('zapisnik_o_polaganju_ispita')
                    ->onDelete('set null');
            }
        });

        Schema::table('zapisnik_o_polaganju_ispita', function (Blueprint $table) {
            if (! $this->foreignKeyExists('zapisnik_o_polaganju_ispita', 'zapisnik_o_polaganju_ispita_predmet_id_foreign')) {
                $table->foreign('predmet_id')
                    ->references('id')->on('predmet')
                    ->onDelete('restrict');
            }
            if (! $this->foreignKeyExists('zapisnik_o_polaganju_ispita', 'zapisnik_o_polaganju_ispita_profesor_id_foreign')) {
                $table->foreign('profesor_id')
                    ->references('id')->on('profesor')
                    ->onDelete('restrict');
            }
            if (! $this->foreignKeyExists('zapisnik_o_polaganju_ispita', 'zapisnik_o_polaganju_ispita_rok_id_foreign')) {
                $table->foreign('rok_id')
                    ->references('id')->on('aktivni_ispitni_rokovi')
                    ->onDelete('restrict');
            }
            if (! $this->foreignKeyExists('zapisnik_o_polaganju_ispita', 'zapisnik_o_polaganju_ispita_prijavaispita_id_foreign')) {
                $table->foreign('prijavaIspita_id')
                    ->references('id')->on('prijava_ispita')
                    ->onDelete('set null');
            }
        });

        Schema::table('zapisnik_o_polaganju__student', function (Blueprint $table) {
            if (! $this->foreignKeyExists('zapisnik_o_polaganju__student', 'zapisnik_o_polaganju__student_zapisnik_id_foreign')) {
                $table->foreign('zapisnik_id')
                    ->references('id')->on('zapisnik_o_polaganju_ispita')
                    ->onDelete('cascade');
            }
            if (! $this->foreignKeyExists('zapisnik_o_polaganju__student', 'zapisnik_o_polaganju__student_kandidat_id_foreign')) {
                $table->foreign('kandidat_id')
                    ->references('id')->on('kandidat')
                    ->onDelete('cascade');
            }
            if (! $this->foreignKeyExists('zapisnik_o_polaganju__student', 'zapisnik_o_polaganju__student_prijavaispita_id_foreign')) {
                $table->foreign('prijavaIspita_id')
                    ->references('id')->on('prijava_ispita')
                    ->onDelete('cascade');
            }
        });

        Schema::table('zapisnik_o_polaganju__studijski_program', function (Blueprint $table) {
            if (! $this->foreignKeyExists('zapisnik_o_polaganju__studijski_program', 'zop_studprogram_zapisnik_fk')) {
                $table->foreign('zapisnik_id', 'zop_studprogram_zapisnik_fk')
                    ->references('id')->on('zapisnik_o_polaganju_ispita')
                    ->onDelete('cascade');
            }
            if (! $this->foreignKeyExists('zapisnik_o_polaganju__studijski_program', 'zop_studprogram_program_fk')) {
                $table->foreign('StudijskiProgram_id', 'zop_studprogram_program_fk')
                    ->references('id')->on('studijski_program')
                    ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        $this->dropForeignIfExists('zapisnik_o_polaganju__studijski_program', 'zop_studprogram_zapisnik_fk');
        $this->dropForeignIfExists('zapisnik_o_polaganju__studijski_program', 'zop_studprogram_program_fk');

        $this->dropForeignIfExists('zapisnik_o_polaganju__student', 'zapisnik_o_polaganju__student_prijavaispita_id_foreign');
        $this->dropForeignIfExists('zapisnik_o_polaganju__student', 'zapisnik_o_polaganju__student_zapisnik_id_foreign');
        $this->dropForeignIfExists('zapisnik_o_polaganju__student', 'zapisnik_o_polaganju__student_kandidat_id_foreign');

        $this->dropForeignIfExists('zapisnik_o_polaganju_ispita', 'zapisnik_o_polaganju_ispita_prijavaispita_id_foreign');
        $this->dropForeignIfExists('zapisnik_o_polaganju_ispita', 'zapisnik_o_polaganju_ispita_predmet_id_foreign');
        $this->dropForeignIfExists('zapisnik_o_polaganju_ispita', 'zapisnik_o_polaganju_ispita_profesor_id_foreign');
        $this->dropForeignIfExists('zapisnik_o_polaganju_ispita', 'zapisnik_o_polaganju_ispita_rok_id_foreign');

        $this->dropForeignIfExists('polozeni_ispiti', 'polozeni_ispiti_kandidat_id_foreign');
        $this->dropForeignIfExists('polozeni_ispiti', 'polozeni_ispiti_predmet_id_foreign');
        $this->dropForeignIfExists('polozeni_ispiti', 'polozeni_ispiti_prijava_id_foreign');
        $this->dropForeignIfExists('polozeni_ispiti', 'polozeni_ispiti_zapisnik_id_foreign');

        $this->dropForeignIfExists('prijava_ispita', 'prijava_ispita_kandidat_id_foreign');
        $this->dropForeignIfExists('prijava_ispita', 'prijava_ispita_predmet_id_foreign');
        $this->dropForeignIfExists('prijava_ispita', 'prijava_ispita_profesor_id_foreign');
        $this->dropForeignIfExists('prijava_ispita', 'prijava_ispita_rok_id_foreign');

        $this->dropForeignIfExists('upis_godine', 'upis_godine_kandidat_id_foreign');
        $this->dropForeignIfExists('upis_godine', 'upis_godine_studijskiprogram_id_foreign');
        $this->dropForeignIfExists('upis_godine', 'upis_godine_tipstudija_id_foreign');
        $this->dropForeignIfExists('upis_godine', 'upis_godine_statusgodine_id_foreign');

        $this->dropForeignIfExists('kandidat', 'kandidat_studijskiprogram_id_foreign');
        $this->dropForeignIfExists('kandidat', 'kandidat_tipstudija_id_foreign');
        $this->dropForeignIfExists('kandidat', 'kandidat_skolskagodinaupisa_id_foreign');
        $this->dropForeignIfExists('kandidat', 'kandidat_statusupisa_id_foreign');
    }
};
