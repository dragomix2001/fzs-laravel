<?php

namespace App\Exports;

use App\Models\Kandidat;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KandidatiExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Kandidat::select(
            'id',
            'imeKandidata',
            'prezimeKandidata',
            'email',
            'jmbg',
            'datumRodjenja',
            'telefon',
            'adresa',
            'tipStudija_id',
            'studijskiProgram_id',
            'skolskaGodinaUpisa_id',
            'statusUpisa_id',
            'BrojIndeksa'
        )->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Ime',
            'Prezime',
            'Email',
            'JMBG',
            'Datum rodjenja',
            'Telefon',
            'Adresa',
            'Tip studija ID',
            'Studijski program ID',
            'Školska godina ID',
            'Status upisa ID',
            'Broj indeksa',
        ];
    }
}
