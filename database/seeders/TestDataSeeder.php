<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TestDataSeeder extends Seeder
{
    private string $timestamp;

    public function run(): void
    {
        $this->timestamp = now()->toDateTimeString();

        $this->seedSifarnici();
        $programs = $this->seedProgramsAndSubjects();
        $professors = $this->seedProfessors();
        $candidates = $this->seedCandidates($programs);
        $this->seedEnrollments($candidates);
        $this->seedExams($candidates, $professors);

        $this->command?->info('Demo podaci su popunjeni hijerarhijskim redom.');
    }

    private function seedSifarnici(): void
    {
        $this->upsertMany('tip_studija', 'naziv', [
            ['naziv' => 'Osnovne akademske studije', 'opis' => 'Cetvorogodisnje studije', 'skrNaziv' => 'OAS', 'indikatorAktivan' => 1],
            ['naziv' => 'Master akademske studije', 'opis' => 'Dvosemestralne studije drugog nivoa', 'skrNaziv' => 'MAS', 'indikatorAktivan' => 1],
        ]);
        foreach ([
            ['naziv' => 'Prva godina', 'nazivRimski' => 'I', 'nazivSlovimaUPadezu' => 'prve', 'redosledPrikazivanja' => 1, 'indikatorAktivan' => 1],
            ['naziv' => 'Druga godina', 'nazivRimski' => 'II', 'nazivSlovimaUPadezu' => 'druge', 'redosledPrikazivanja' => 2, 'indikatorAktivan' => 1],
            ['naziv' => 'Treca godina', 'nazivRimski' => 'III', 'nazivSlovimaUPadezu' => 'trece', 'redosledPrikazivanja' => 3, 'indikatorAktivan' => 1],
            ['naziv' => 'Cetvrta godina', 'nazivRimski' => 'IV', 'nazivSlovimaUPadezu' => 'cetvrte', 'redosledPrikazivanja' => 4, 'indikatorAktivan' => 1],
        ] as $year) {
            $this->upsert('godina_studija', ['naziv' => $year['naziv']], $year);
        }
        foreach (['2022/2023', '2023/2024', '2024/2025', '2025/2026'] as $schoolYear) {
            $this->upsert('skolska_god_upisa', ['naziv' => $schoolYear], ['naziv' => $schoolYear]);
        }
        $this->upsertMany('status_profesora', 'naziv', [
            ['naziv' => 'Aktivan', 'indikatorAktivan' => 1], ['naziv' => 'Na odsustvu', 'indikatorAktivan' => 1],
        ]);
        $this->upsertMany('status_kandidata', 'naziv', [
            ['naziv' => 'Aktivan student', 'indikatorAktivan' => 1], ['naziv' => 'Diplomirao', 'indikatorAktivan' => 1],
        ]);
        $this->upsertMany('status_studiranja', 'naziv', [
            ['naziv' => 'Aktivan', 'indikatorAktivan' => 1], ['naziv' => 'Mirovanje', 'indikatorAktivan' => 1], ['naziv' => 'Zavrsio studije', 'indikatorAktivan' => 1],
        ]);
        $this->upsertMany('status_ispita', 'naziv', [
            ['naziv' => 'Prijavljen', 'indikatorAktivan' => 1], ['naziv' => 'Polozio', 'indikatorAktivan' => 1], ['naziv' => 'Nije polozen', 'indikatorAktivan' => 1],
        ]);
        $this->upsertMany('tip_predmeta', 'naziv', [
            ['naziv' => 'Obavezni', 'skrNaziv' => 'OB', 'indikatorAktivan' => 1], ['naziv' => 'Izborni', 'skrNaziv' => 'IZB', 'indikatorAktivan' => 1],
        ]);
        $this->upsertMany('tip_prijave', 'naziv', [
            ['naziv' => 'Redovna prijava', 'indikatorAktivan' => 1], ['naziv' => 'Ponovljena prijava', 'indikatorAktivan' => 1],
        ]);
        $this->upsertMany('semestar', 'naziv', [
            ['naziv' => 'Zimski semestar', 'nazivRimski' => 'I', 'nazivBrojcano' => 1, 'indikatorAktivan' => 1],
            ['naziv' => 'Letnji semestar', 'nazivRimski' => 'II', 'nazivBrojcano' => 2, 'indikatorAktivan' => 1],
        ]);
        $this->upsertMany('ispitni_rok', 'naziv', [
            ['naziv' => 'Januarski rok', 'indikatorAktivan' => 1], ['naziv' => 'Junski rok', 'indikatorAktivan' => 1], ['naziv' => 'Septembarski rok', 'indikatorAktivan' => 1],
        ]);
        $this->upsertMany('sport', 'naziv', [
            ['naziv' => 'Kosarka', 'indikatorAktivan' => 1], ['naziv' => 'Odbojka', 'indikatorAktivan' => 1], ['naziv' => 'Atletika', 'indikatorAktivan' => 1],
        ]);
        $this->upsertMany('krsna_slava', 'naziv', [
            ['naziv' => 'Sveti Nikola', 'datumSlave' => '19.12.', 'indikatorAktivan' => 1], ['naziv' => 'Sveti Djordje', 'datumSlave' => '06.05.', 'indikatorAktivan' => 1],
        ]);
        $this->upsertMany('opsti_uspeh', 'naziv', [['naziv' => 'Vrlo dobar'], ['naziv' => 'Odlican']]);
        $this->upsertMany('srednje_skole_fakulteti', 'naziv', [
            ['naziv' => 'Gimnazija Svetozar Markovic', 'indSkoleFakulteta' => 1], ['naziv' => 'Sportska gimnazija', 'indSkoleFakulteta' => 1],
        ]);

        $this->upsert('region', ['naziv' => 'Beogradski region'], ['naziv' => 'Beogradski region']);
        $regionId = DB::table('region')->where('naziv', 'Beogradski region')->value('id');
        $this->upsert('opstina', ['naziv' => 'Novi Beograd'], ['naziv' => 'Novi Beograd', 'region_id' => $regionId]);
        $opstinaId = DB::table('opstina')->where('naziv', 'Novi Beograd')->value('id');
        $this->upsert('mesto', ['naziv' => 'Beograd'], ['naziv' => 'Beograd', 'opstina_id' => $opstinaId]);
    }

    private function seedProgramsAndSubjects(): array
    {
        $types = DB::table('tip_studija')->pluck('id', 'skrNaziv');
        $years = DB::table('godina_studija')->pluck('id', 'redosledPrikazivanja');
        $schoolYear = DB::table('skolska_god_upisa')->where('naziv', '2025/2026')->value('id');
        $requiredType = DB::table('tip_predmeta')->where('skrNaziv', 'OB')->value('id');
        $programs = [];

        foreach ([
            ['name' => 'Sport i fizicko vaspitanje', 'short' => 'SIV', 'type' => 'OAS', 'title' => 'Profesor fizickog vaspitanja'],
            ['name' => 'Sportski trener', 'short' => 'ST', 'type' => 'OAS', 'title' => 'Strucni trener'],
            ['name' => 'Menadzment u sportu', 'short' => 'MUS', 'type' => 'MAS', 'title' => 'Master menadzer u sportu'],
        ] as $program) {
            $programId = $this->upsert('studijski_program', ['skrNazivStudijskogPrograma' => $program['short']], [
                'naziv' => $program['name'], 'skrNazivStudijskogPrograma' => $program['short'], 'zvanje' => $program['title'],
                'tipStudija_id' => $types[$program['type']], 'indikatorAktivan' => 1,
            ]);
            $programs[$program['short']] = ['id' => $programId, 'type' => $types[$program['type']]];
            $subjects = $program['type'] === 'MAS'
                ? ['Analiza sportskih sistema', 'Organizacija sportskih dogadjaja', 'Strateski menadzment']
                : ['Anatomija', 'Fiziologija sporta', 'Teorija sporta', 'Sportske igre'];
            foreach ($subjects as $index => $subjectName) {
                $this->upsert('predmet', ['naziv' => $subjectName], ['naziv' => $subjectName]);
                $subjectId = DB::table('predmet')->where('naziv', $subjectName)->value('id');
                $yearNumber = ($index % ($program['type'] === 'MAS' ? 2 : 4)) + 1;
                $this->upsert('predmet_program', ['studijskiProgram_id' => $programId, 'predmet_id' => $subjectId, 'godinaStudija_id' => $years[$yearNumber]], [
                    'studijskiProgram_id' => $programId, 'godinaStudija_id' => $years[$yearNumber], 'semestar' => ($index % 2) + 1,
                    'predmet_id' => $subjectId, 'tipPredmeta_id' => $requiredType, 'tipStudija_id' => $types[$program['type']], 'espb' => 6,
                    'statusPredmeta' => 1, 'predavanja' => 30, 'vezbe' => 30, 'skolskaGodina_id' => $schoolYear, 'indikatorAktivan' => 1,
                ]);
            }
        }

        return $programs;
    }

    private function seedProfessors(): array
    {
        $status = DB::table('status_profesora')->where('naziv', 'Aktivan')->value('id');
        $professors = [];
        foreach ([
            ['name' => 'Jovan', 'surname' => 'Jovanovic', 'mail' => 'jovan@fzs.edu.rs'],
            ['name' => 'Marija', 'surname' => 'Markovic', 'mail' => 'marija@fzs.edu.rs'],
            ['name' => 'Stevan', 'surname' => 'Stevanovic', 'mail' => 'stevan@fzs.edu.rs'],
        ] as $index => $professor) {
            $professors[] = $this->upsert('profesor', ['mail' => $professor['mail']], [
                'jmbg' => '1901990'.str_pad((string) (100 + $index), 6, '0', STR_PAD_LEFT), 'ime' => $professor['name'], 'prezime' => $professor['surname'],
                'telefon' => '011/555-'.str_pad((string) (100 + $index), 3, '0', STR_PAD_LEFT), 'status_id' => $status, 'zvanje' => 'Vanredni profesor',
                'kabinet' => 'Kabinet '.(12 + $index), 'mail' => $professor['mail'], 'indikatorAktivan' => 1,
            ]);
        }
        foreach (DB::table('predmet_program')->pluck('id') as $index => $subjectProgramId) {
            $this->upsert('profesor_predmet', ['profesor_id' => $professors[$index % count($professors)], 'predmet_id' => $subjectProgramId], [
                'profesor_id' => $professors[$index % count($professors)], 'predmet_id' => $subjectProgramId, 'indikatorAktivan' => 1,
            ]);
        }

        return $professors;
    }

    private function seedCandidates(array $programs): array
    {
        $schoolYear = DB::table('skolska_god_upisa')->where('naziv', '2025/2026')->value('id');
        $status = DB::table('status_studiranja')->where('naziv', 'Aktivan')->value('id') ?: 1;
        $place = DB::table('mesto')->where('naziv', 'Beograd')->value('id');
        $slava = DB::table('krsna_slava')->where('naziv', 'Sveti Nikola')->value('id');
        $school = DB::table('srednje_skole_fakulteti')->where('naziv', 'Sportska gimnazija')->value('id');
        $success = DB::table('opsti_uspeh')->where('naziv', 'Odlican')->value('id');
        $candidates = [];
        $people = [
            ['Petar', 'Petrovic', 'SIV', 1], ['Ana', 'Anic', 'SIV', 2], ['Milos', 'Milosevic', 'ST', 1], ['Sofija', 'Stojkovic', 'ST', 3],
            ['Luka', 'Lukovic', 'SIV', 4], ['Jelena', 'Jovanovic', 'MUS', 1], ['Nikola', 'Nikolic', 'MUS', 2], ['Teodora', 'Todorovic', 'SIV', 2],
            ['Marko', 'Markovic', 'ST', 1], ['Ivana', 'Ilic', 'SIV', 3],
        ];
        foreach ($people as $index => [$name, $surname, $programShort, $year]) {
            $email = strtolower($name.'.'.$surname).'@student.fzs.edu.rs';
            $program = $programs[$programShort];
            $candidateId = $this->upsert('kandidat', ['email' => $email], [
                'imeKandidata' => $name, 'prezimeKandidata' => $surname, 'jmbg' => '0101990'.str_pad((string) (100 + $index), 6, '0', STR_PAD_LEFT),
                'datumRodjenja' => (1998 + ($index % 5)).'-'.str_pad((string) (($index % 9) + 1), 2, '0', STR_PAD_LEFT).'-15 00:00:00', 'mestoRodjenja' => 'Beograd',
                'krsnaSlava_id' => $slava, 'kontaktTelefon' => '064/555-'.str_pad((string) (100 + $index), 3, '0', STR_PAD_LEFT), 'adresaStanovanja' => 'Bulevar sporta '.(10 + $index),
                'email' => $email, 'imePrezimeJednogRoditelja' => 'Roditelj '.$surname, 'srednjeSkoleFakulteti' => 'Sportska gimnazija',
                'mestoZavrseneSkoleFakulteta' => 'Beograd', 'smerZavrseneSkoleFakulteta' => 'Sportski smer', 'uspehSrednjaSkola_id' => $school,
                'opstiUspehSrednjaSkola_id' => $success, 'srednjaOcenaSrednjaSkola' => 4.20 + (($index % 8) / 10), 'telesnaTezina' => 62 + ($index * 3), 'visina' => 168 + ($index % 8),
                'statusUpisa_id' => $status, 'brojBodovaTest' => 28 + ($index % 12), 'brojBodovaSkola' => 32 + ($index % 15), 'ukupniBrojBodova' => 60 + ($index % 20),
                'prosecnaOcena' => 7.80 + (($index % 8) / 10), 'upisniRok' => 'Jun 2025', 'brojIndeksa' => str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT).'/2025',
                'skolskaGodinaUpisa_id' => $schoolYear, 'indikatorAktivan' => 1, 'studijskiProgram_id' => $program['id'], 'tipStudija_id' => $program['type'],
                'godinaStudija_id' => DB::table('godina_studija')->where('redosledPrikazivanja', $year)->value('id'), 'mesto_id' => $place, 'uplata' => 1, 'upisan' => 1,
                'drzavaZavrseneSkole' => 'Srbija', 'drzavaRodjenja' => 'Srbija', 'godinaZavrsetkaSkole' => '2025', 'datumStatusa' => now(),
            ]);
            $candidates[] = ['id' => $candidateId, 'program' => $program, 'year' => $year];
            if (Schema::hasTable('sportsko_angazovanje')) {
                $sportId = ($index % 3) + 1;
                $engagementId = $this->upsert('sportsko_angazovanje', ['kandidat_id' => $candidateId, 'sport_id' => $sportId], [
                    'sport_id' => $sportId, 'kandidat_id' => $candidateId, 'nazivKluba' => 'FZS klub '.$program['id'], 'odDoGodina' => '2020-2025', 'ukupnoGodina' => 5,
                ]);
                DB::table('kandidat')->where('id', $candidateId)->update(['sportskoAngazovanje_id' => $engagementId]);
            }
        }

        return $candidates;
    }

    private function seedEnrollments(array $candidates): void
    {
        $schoolYear = DB::table('skolska_god_upisa')->where('naziv', '2025/2026')->value('id');
        foreach ($candidates as $candidate) {
            $this->upsert('upis_godine', ['kandidat_id' => $candidate['id'], 'godina' => $candidate['year']], [
                'kandidat_id' => $candidate['id'], 'godina' => $candidate['year'], 'pokusaj' => 1, 'tipStudija_id' => $candidate['program']['type'], 'statusGodine_id' => 1,
                'studijskiProgram_id' => $candidate['program']['id'], 'skolskaGodina_id' => $schoolYear, 'datumUpisa' => '2025-10-01', 'datumPromene' => '2025-10-01',
            ]);
        }
    }

    private function seedExams(array $candidates, array $professors): void
    {
        $examPeriod = DB::table('ispitni_rok')->where('naziv', 'Junski rok')->first();
        $activePeriod = $this->upsert('aktivni_ispitni_rokovi', ['naziv' => 'Junski rok 2026'], [
            'rok_id' => $examPeriod->id, 'naziv' => 'Junski rok 2026', 'pocetak' => '2026-06-01', 'kraj' => '2026-06-30', 'tipRoka_id' => 1,
            'komentar' => 'Demo rok za testiranje prijava i zapisnika', 'indikatorAktivan' => 1,
        ]);
        $applicationType = DB::table('tip_prijave')->where('naziv', 'Redovna prijava')->value('id');
        $subjectPrograms = DB::table('predmet_program')->orderBy('id')->get();
        $candidateIds = array_column($candidates, 'id');
        $oldApplications = DB::table('prijava_ispita')->whereIn('kandidat_id', $candidateIds)->pluck('id');
        $oldMinutes = DB::table('zapisnik_o_polaganju_ispita')->whereIn('prijavaIspita_id', $oldApplications)->pluck('id');
        DB::table('polozeni_ispiti')->whereIn('prijava_id', $oldApplications)->delete();
        DB::table('zapisnik_o_polaganju__student')->whereIn('prijavaIspita_id', $oldApplications)->delete();
        DB::table('zapisnik_o_polaganju__studijski_program')->whereIn('zapisnik_id', $oldMinutes)->delete();
        DB::table('zapisnik_o_polaganju_ispita')->whereIn('id', $oldMinutes)->delete();
        DB::table('prijava_ispita')->whereIn('id', $oldApplications)->delete();
        foreach ($candidates as $index => $candidate) {
            $programSubjects = $subjectPrograms
                ->where('studijskiProgram_id', $candidate['program']['id'])
                ->values();
            $subjectProgram = $programSubjects->isNotEmpty()
                ? $programSubjects[$index % $programSubjects->count()]
                : $subjectPrograms[$index % $subjectPrograms->count()];
            $subjectId = $subjectProgram->predmet_id;
            $date = '2026-06-'.str_pad((string) (($index % 9) + 10), 2, '0', STR_PAD_LEFT);
            $applicationId = $this->upsert('prijava_ispita', ['kandidat_id' => $candidate['id'], 'predmet_id' => $subjectProgram->id, 'rok_id' => $activePeriod], [
                'kandidat_id' => $candidate['id'], 'predmet_id' => $subjectProgram->id, 'profesor_id' => $professors[$index % count($professors)], 'rok_id' => $activePeriod,
                'brojPolaganja' => 1, 'datum' => $date, 'datum2' => $date, 'vreme' => '09:00:00', 'tipPrijave_id' => $applicationType,
            ]);
            $this->upsert('zapisnik_o_polaganju_ispita', ['prijavaIspita_id' => $applicationId], [
                'predmet_id' => $subjectId, 'rok_id' => $activePeriod, 'prijavaIspita_id' => $applicationId, 'datum' => $date, 'datum2' => $date,
                'vreme' => sprintf('%02d:00:00', 9 + ($index % 4)), 'ucionica' => 'Sala '.(($index % 3) + 1), 'profesor_id' => $professors[$index % count($professors)], 'kandidat_id' => $candidate['id'],
            ]);
            $zapisnikId = DB::table('zapisnik_o_polaganju_ispita')->where('prijavaIspita_id', $applicationId)->value('id');
            $this->upsert('zapisnik_o_polaganju__student', ['kandidat_id' => $candidate['id'], 'zapisnik_id' => $zapisnikId], ['kandidat_id' => $candidate['id'], 'prijavaIspita_id' => $applicationId, 'zapisnik_id' => $zapisnikId]);
            $this->upsert('zapisnik_o_polaganju__studijski_program', ['zapisnik_id' => $zapisnikId, 'StudijskiProgram_id' => $candidate['program']['id']], ['zapisnik_id' => $zapisnikId, 'StudijskiProgram_id' => $candidate['program']['id']]);
            if (Schema::hasTable('polozeni_ispiti')) {
                $this->upsert('polozeni_ispiti', ['prijava_id' => $applicationId], [
                    'prijava_id' => $applicationId, 'zapisnik_id' => $zapisnikId, 'kandidat_id' => $candidate['id'], 'predmet_id' => $subjectProgram->id,
                    'ocenaPismeni' => 8, 'ocenaUsmeni' => 9, 'konacnaOcena' => 9, 'brojBodova' => 82, 'statusIspita' => 2, 'odluka_id' => 1, 'indikatorAktivan' => 1,
                ]);
            }
        }
    }

    private function upsertMany(string $table, string $key, array $rows): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }
        foreach ($rows as $row) {
            $this->upsert($table, [$key => $row[$key]], $row);
        }
    }

    private function upsert(string $table, array $keys, array $values): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }
        $values['updated_at'] = $this->timestamp;
        $values['created_at'] = $values['created_at'] ?? $this->timestamp;
        DB::table($table)->updateOrInsert($keys, $values);

        return (int) DB::table($table)->where($keys)->value('id');
    }
}
