<?php

namespace App\DTOs;

use Illuminate\Http\Request;

readonly class DiplomaAddData
{
    public function __construct(
        public int $kandidatId,
        public ?string $brojDiplome,
        public ?string $datumOdbrane,
        public ?string $nazivStudijskogPrograma,
        public ?string $brojPocetnogLista,
        public ?string $brojZapisnika,
        public ?string $datum,
        public ?string $pristupniRad,
        public ?string $tema,
        public ?string $mentor,
        public ?string $ocena,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            kandidatId: (int) $request->input('kandidat_id'),
            brojDiplome: $request->input('brojDiplome'),
            datumOdbrane: $request->input('datumOdbrane'),
            nazivStudijskogPrograma: $request->input('nazivStudijskogPrograma'),
            brojPocetnogLista: $request->input('brojPocetnogLista'),
            brojZapisnika: $request->input('brojZapisnika'),
            datum: $request->input('datum'),
            pristupniRad: $request->input('pristupniRad'),
            tema: $request->input('tema'),
            mentor: $request->input('mentor'),
            ocena: $request->input('ocena')
        );
    }
}
