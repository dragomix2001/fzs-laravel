<?php

namespace App\Services;

use App\Models\PolozeniIspiti;
use App\Models\ZapisnikOPolaganju_Student;
use App\Models\ZapisnikOPolaganju_StudijskiProgram;
use App\Models\ZapisnikOPolaganjuIspita;

class IspitCleanupService
{
    public function deleteZapisnik(int $id): void
    {
        ZapisnikOPolaganju_Student::where('zapisnik_id', $id)->delete();
        ZapisnikOPolaganju_StudijskiProgram::where('zapisnik_id', $id)->delete();
        ZapisnikOPolaganjuIspita::destroy($id);
    }

    public function deletePolozeniIspit(int $id, int $brisiZapisnik): void
    {
        $ispit = PolozeniIspiti::find($id);

        if ($brisiZapisnik === 1) {
            ZapisnikOPolaganju_Student::where([
                'zapisnik_id' => $ispit->zapisnik_id,
                'kandidat_id' => $ispit->kandidat_id,
            ])->delete();
            PolozeniIspiti::destroy($id);

            if (ZapisnikOPolaganju_Student::where('zapisnik_id', $ispit->zapisnik_id)->count() === 0) {
                ZapisnikOPolaganjuIspita::destroy($ispit->zapisnik_id);
            }

            return;
        }

        $ispit->update([
            'indikatorAktivan' => false,
            'ocenaPismeni' => 0,
            'ocenaUsmeni' => 0,
            'konacnaOcena' => 0,
            'brojBodova' => 0,
            'statusIspita' => 0,
        ]);
    }

    public function deletePrivremeniIspit(int $id): void
    {
        PolozeniIspiti::find($id)->delete();
    }
}
