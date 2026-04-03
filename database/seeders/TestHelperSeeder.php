<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestHelperSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'FZS Admin',
            'email' => 'fzs@fzs.rs',
            'password' => Hash::make('fzs123'),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tip_studija')->insert([
            ['id' => 1, 'naziv' => 'Osnovne akademske studije', 'skrNaziv' => 'OAS', 'indikatorAktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'naziv' => 'Master akademske studije', 'skrNaziv' => 'MAS', 'indikatorAktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'naziv' => 'Doktorske akademske studije', 'skrNaziv' => 'DAS', 'indikatorAktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('studijski_program')->insert([
            ['id' => 1, 'naziv' => 'Sport i fizičko vaspitanje', 'skrNazivStudijskogPrograma' => 'SFV', 'zvanje' => 'Profesor fizičkog vaspitanja', 'tipStudija_id' => 1, 'indikatorAktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('status_studiranja')->insert([
            ['id' => 1, 'naziv' => 'upis u toku', 'indikatorAktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'naziv' => 'upis završen', 'indikatorAktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'naziv' => 'odustao', 'indikatorAktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'naziv' => 'diplomirao', 'indikatorAktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('skolska_god_upisa')->insert([
            ['id' => 1, 'naziv' => '2024/2025', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('godina_studija')->insert([
            ['id' => 1, 'naziv' => 'Prva', 'nazivRimski' => 'I', 'nazivSlovimaUPadezu' => 'prvoj', 'redosledPrikazivanja' => 1, 'indikatorAktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'naziv' => 'Druga', 'nazivRimski' => 'II', 'nazivSlovimaUPadezu' => 'drugoj', 'redosledPrikazivanja' => 2, 'indikatorAktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'naziv' => 'Treća', 'nazivRimski' => 'III', 'nazivSlovimaUPadezu' => 'trećoj', 'redosledPrikazivanja' => 3, 'indikatorAktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'naziv' => 'Četvrta', 'nazivRimski' => 'IV', 'nazivSlovimaUPadezu' => 'četvrtoj', 'redosledPrikazivanja' => 4, 'indikatorAktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('krsna_slava')->insert([
            ['id' => 1, 'naziv' => 'Sveti Nikola', 'datumSlave' => '19.12', 'indikatorAktivan' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('opsti_uspeh')->insert([
            ['id' => 1, 'naziv' => 'odličan', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'naziv' => 'vrlo dobar', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'naziv' => 'dobar', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('region')->insert([
            ['id' => 1, 'naziv' => 'Beograd', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('opstina')->insert([
            ['id' => 1, 'naziv' => 'Stari Grad', 'region_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('mesto')->insert([
            ['id' => 1, 'naziv' => 'Beograd', 'opstina_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('kandidat')->insert([
            [
                'imeKandidata' => 'Petar',
                'prezimeKandidata' => 'Petrović',
                'jmbg' => '0101990710123',
                'krsnaSlava_id' => 1,
                'uspehSrednjaSkola_id' => 1,
                'opstiUspehSrednjaSkola_id' => 1,
                'statusUpisa_id' => 1,
                'skolskaGodinaUpisa_id' => 1,
                'studijskiProgram_id' => 1,
                'tipStudija_id' => 1,
                'godinaStudija_id' => 1,
                'mesto_id' => 1,
                'indikatorAktivan' => 1,
                'uplata' => 0,
                'upisan' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $kandidatId = DB::table('kandidat')->value('id');

        DB::table('upis_godine')->insert([
            [
                'kandidat_id' => $kandidatId,
                'godina' => 1,
                'pokusaj' => 1,
                'tipStudija_id' => 1,
                'statusGodine_id' => 1,
                'studijskiProgram_id' => 1,
                'datumUpisa' => '2024-10-01',
                'datumPromene' => '2024-10-15',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
