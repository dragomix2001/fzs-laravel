<?php

namespace App\Imports;

use App\Models\Kandidat;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KandidatiImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Kandidat([
            'imeKandidata' => $row['ime'] ?? $row['imekandidata'] ?? null,
            'prezimeKandidata' => $row['prezime'] ?? $row['prezimekandidata'] ?? null,
            'email' => $row['email'] ?? null,
            'jmbg' => $row['jmbg'] ?? null,
            'datumRodjenja' => $row['datum_rodjenja'] ?? $row['datumrodjenja'] ?? null,
            'telefon' => $row['telefon'] ?? null,
            'adresa' => $row['adresa'] ?? null,
            'tipStudija_id' => $row['tip_studija_id'] ?? $row['tipstudijaid'] ?? 1,
            'studijskiProgram_id' => $row['studijski_program_id'] ?? $row['studijskiprogramid'] ?? 1,
            'skolskaGodinaUpisa_id' => $row['skolska_godina_id'] ?? $row['skolskagodinaid'] ?? 1,
            'statusUpisa_id' => $row['status_upisa_id'] ?? $row['statusupisaid'] ?? 1,
            'BrojIndeksa' => $row['broj_indeksa'] ?? $row['brojindeksa'] ?? null,
        ]);
    }
}
