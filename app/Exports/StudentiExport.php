<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentiExport implements FromCollection, WithHeadings
{
    protected $studenti;

    public function __construct($studenti)
    {
        $this->studenti = $studenti;
    }

    public function collection()
    {
        return $this->studenti;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Ime',
            'Prezime',
            'Email',
            'Broj indeksa',
            'Tip studija',
            'Studijski program',
            'Školska godina',
            'Status',
        ];
    }
}
