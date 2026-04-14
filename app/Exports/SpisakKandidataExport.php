<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SpisakKandidataExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected int $godina,
    ) {}

    public function collection()
    {
        $statusi = ['1', '2', '4', '5', '7'];

        return DB::table('kandidat')
            ->join('studijski_program', 'kandidat.studijskiProgram_id', '=', 'studijski_program.id')
            ->whereIn('kandidat.statusUpisa_id', $statusi)
            ->where('kandidat.skolskaGodinaUpisa_id', $this->godina)
            ->select(
                'kandidat.ime',
                'kandidat.prezimeKandidata',
                'kandidat.brojIndeksa',
                'studijski_program.naziv as program'
            )
            ->orderByRaw('SUBSTR(kandidat.brojIndeksa, 5)')
            ->orderBy('kandidat.brojIndeksa')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Име',
            'Презиме',
            'Број индекса',
            'Програм',
        ];
    }
}
