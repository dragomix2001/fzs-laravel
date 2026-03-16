<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('users')->count() > 4) {
            $this->command->info('Test data already exists, skipping...');
            return;
        }
        
        $this->command->info('Креирање тест података...');

        // Users
        $users = [
            ['name' => 'Админ Систем', 'email' => 'admin@fzs.edu.rs', 'password' => Hash::make('admin123'), 'role' => 'admin', 'created_at' => now()],
            ['name' => 'Проф. Јован Јовановић', 'email' => 'jovan@fzs.edu.rs', 'password' => Hash::make('prof123'), 'role' => 'professor', 'created_at' => now()],
            ['name' => 'Проф. Марија Марковић', 'email' => 'marija@fzs.edu.rs', 'password' => Hash::make('prof123'), 'role' => 'professor', 'created_at' => now()],
            ['name' => 'Студент Петар Петровић', 'email' => 'petar@student.fzs.edu.rs', 'password' => Hash::make('student123'), 'role' => 'student', 'created_at' => now()],
            ['name' => 'Студент Ana Анић', 'email' => 'ana@student.fzs.edu.rs', 'password' => Hash::make('student123'), 'role' => 'student', 'created_at' => now()],
        ];
        
        $userIds = [];
        foreach ($users as $user) {
            $id = DB::table('users')->insertGetId($user);
            $userIds[$user['role']] = $id;
        }
        
        $this->command->info('Креирано ' . count($users) . ' корисника');

        // Školska godina
        $godinaId = DB::table('skolska_god_upisa')->insertGetId([
            'naziv' => '2024/2025',
            'aktivan' => true,
            'created_at' => now(),
        ]);

        // Tip studija
        $tipStudijaId = DB::table('tip_studija')->insertGetId([
            'naziv' => 'Основне академске студије',
            'opis' => '4 godine',
            'skrNaziv' => 'OS',
            'indikatorAktivan' => 1,
            'created_at' => now(),
        ]);

        // Studijski program
        $programId = DB::table('studijski_program')->insertGetId([
            'naziv' => 'Спорт и физичко васпитање',
            'skrNazivStudijskogPrograma' => 'SIV',
            'zvanje' => 'Професор физичког васпитања',
            'tipStudija_id' => $tipStudijaId,
            'indikatorAktivan' => 1,
            'created_at' => now(),
        ]);

        // Status upisa
        $statusUpisaId = DB::table('status_upisa')->insertGetId([
            'naziv' => 'Активан',
            'created_at' => now(),
        ]);

        // Profesori
        $profesori = [
            ['ime' => 'Јован', 'prezime' => 'Јовановић', 'email' => 'jovan@fzs.edu.rs', 'telefon' => '011/123-4567', 'created_at' => now()],
            ['ime' => 'Марија', 'prezime' => 'Марковић', 'email' => 'marija@fzs.edu.rs', 'telefon' => '011/123-4568', 'created_at' => now()],
            ['ime' => 'Стеван', 'prezime' => 'Стевановић', 'email' => 'stevan@fzs.edu.rs', 'telefon' => '011/123-4569', 'created_at' => now()],
        ];
        
        $profesorIds = [];
        foreach ($profesori as $prof) {
            $id = DB::table('profesors')->insertGetId($prof);
            $profesorIds[] = $id;
        }
        
        $this->command->info('Креирано ' . count($profesori) . ' професора');

        // Studenti (kandidati)
        $studenti = [
            ['imeKandidata' => 'Петар', 'prezimeKandidata' => 'Петровић', 'email' => 'petar@student.fzs.edu.rs', 'BrojIndeksa' => '001/2024', 'tipStudija_id' => $tipStudijaId, 'studijskiProgram_id' => $programId, 'skolskaGodinaUpisa_id' => $godinaId, 'statusUpisa_id' => $statusUpisaId, 'created_at' => now()],
            ['imeKandidata' => 'Ana', 'prezimeKandidata' => 'Анић', 'email' => 'ana@student.fzs.edu.rs', 'BrojIndeksa' => '002/2024', 'tipStudija_id' => $tipStudijaId, 'studijskiProgram_id' => $programId, 'skolskaGodinaUpisa_id' => $godinaId, 'statusUpisa_id' => $statusUpisaId, 'created_at' => now()],
            ['imeKandidata' => 'Милош', 'prezimeKandidata' => 'Милошевић', 'email' => 'milos@student.fzs.edu.rs', 'BrojIndeksa' => '003/2024', 'tipStudija_id' => $tipStudijaId, 'studijskiProgram_id' => $programId, 'skolskaGodinaUpisa_id' => $godinaId, 'statusUpisa_id' => $statusUpisaId, 'created_at' => now()],
            ['imeKandidata' => 'Софија', 'prezimeKandidata' => 'Стојковић', 'email' => 'sofija@student.fzs.edu.rs', 'BrojIndeksa' => '004/2024', 'tipStudija_id' => $tipStudijaId, 'studijskiProgram_id' => $programId, 'skolskaGodinaUpisa_id' => $godinaId, 'statusUpisa_id' => $statusUpisaId, 'created_at' => now()],
            ['imeKandidata' => 'Лука', 'prezimeKandidata' => 'Луковић', 'email' => 'luka@student.fzs.edu.rs', 'BrojIndeksa' => '005/2024', 'tipStudija_id' => $tipStudijaId, 'studijskiProgram_id' => $programId, 'skolskaGodinaUpisa_id' => $godinaId, 'statusUpisa_id' => $statusUpisaId, 'created_at' => now()],
        ];
        
        $studentIds = [];
        foreach ($studenti as $student) {
            $id = DB::table('kandidats')->insertGetId($student);
            $studentIds[] = $id;
        }
        
        $this->command->info('Креирано ' . count($studenti) . ' студената');

        // Predmeti
        $predmeti = [
            ['naziv' => 'Спортске игре 1', 'sifra' => 'СПИ1', 'espb' => 6, 'created_at' => now()],
            ['naziv' => 'Анатомија', 'sifra' => 'АНАТ', 'espb' => 8, 'created_at' => now()],
            ['naziv' => 'Физиологија', 'sifra' => 'ФИЗИО', 'espb' => 8, 'created_at' => now()],
            ['naziv' => 'Теорија спорта', 'sifra' => 'ТЕОСП', 'espb' => 4, 'created_at' => now()],
            ['naziv' => 'Спортске игре 2', 'sifra' => 'СПИ2', 'espb' => 6, 'created_at' => now()],
        ];
        
        $predmetIds = [];
        foreach ($predmeti as $predmet) {
            $id = DB::table('predmets')->insertGetId($predmet);
            $predmetIds[] = $id;
        }
        
        $this->command->info('Креирано ' . count($predmeti) . ' предмета');

        // Obaveštenja
        $obavestenja = [
            ['naslov' => 'Почетак семестра', 'sadrzaj' => 'Настава почиње 1. октобра 2024. године.', 'tip' => 'opste', 'aktivan' => true, 'profesor_id' => $profesorIds[0], 'datum_objave' => now(), 'created_at' => now()],
            ['naslov' => 'Испитни рок', 'prijavaPocetak' => now(), 'prijavaKraj' => now()->addDays(7), 'ispitPocetak' => now()->addDays(14), 'aktivan' => true, 'created_at' => now()],
            ['naslov' => 'Распоред часова', 'sadrzaj' => 'Распоред часова за зимски семестар је објављен.', 'tip' => 'raspored', 'aktivan' => true, 'profesor_id' => $profesorIds[1], 'datum_objave' => now(), 'created_at' => now()],
        ];
        
        foreach ($obavestenja as $obav) {
            DB::table('obavestenjes')->insert($obav);
        }
        
        $this->command->info('Креирано ' . count($obavestenja) . ' обавештења');

        // Raspored
        $raspored = [
            ['predmet_id' => $predmetIds[0], 'profesor_id' => $profesorIds[0], 'dan' => 'Понедељак', 'vreme_pocetka' => '08:00', 'vreme_kraja' => '09:30', 'ucionica' => 'Сала 1', 'aktivan' => true, 'created_at' => now()],
            ['predmet_id' => $predmetIds[1], 'profesor_id' => $profesorIds[1], 'dan' => 'Понедељак', 'vreme_pocetka' => '10:00', 'vreme_kraja' => '11:30', 'ucionica' => 'Сала 2', 'aktivan' => true, 'created_at' => now()],
            ['predmet_id' => $predmetIds[2], 'profesor_id' => $profesorIds[2], 'dan' => 'Уторак', 'vreme_pocetka' => '08:00', 'vreme_kraja' => '09:30', 'ucionica' => 'Сала 1', 'aktivan' => true, 'created_at' => now()],
        ];
        
        foreach ($raspored as $rasp) {
            DB::table('rasporeds')->insert($rasp);
        }
        
        $this->command->info('Креирано ' . count($raspored) . ' ставки распореда');

        $this->command->info('Тест подаци успешно креирани!');
    }
}
