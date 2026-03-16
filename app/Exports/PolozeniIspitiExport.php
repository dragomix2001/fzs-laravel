<?php

namespace App\Exports;

use App\Models\PolozeniIspiti;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PolozeniIspitiExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return PolozeniIspiti::with(['kandidat', 'predmet'])
            ->where('indikatorAktivan', 1)
            ->get()
            ->map(function ($ispit) {
                return [
                    'student' => $ispit->kandidat->imeKandidata.' '.$ispit->kandidat->prezimeKandidata,
                    'indeks' => $ispit->kandidat->BrojIndeksa,
                    'predmet' => $ispit->predmet->naziv ?? 'N/A',
                    'ocena' => $ispit->konacnaOcena,
                    'espb' => $ispit->predmet->espb ?? 0,
                    'datum' => $ispit->created_at->format('d.m.Y'),
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Студент',
            'Број индекса',
            'Предмет',
            'Оцена',
            'ЕСПБ',
            'Датум',
        ];
    }
}
