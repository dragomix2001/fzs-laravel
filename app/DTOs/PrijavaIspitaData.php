<?php

namespace App\DTOs;

use Illuminate\Http\Request;

readonly class PrijavaIspitaData
{
    public function __construct(
        public int $kandidatId,
        public int $predmetId,
        public int $profesorId,
        public int $rokId,
        public int $brojPolaganja,
        public ?string $datum = null,
        public ?int $tipPrijaveId = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            kandidatId: (int) $request->input('kandidat_id'),
            predmetId: (int) $request->input('predmet_id'),
            profesorId: (int) $request->input('profesor_id'),
            rokId: (int) $request->input('rok_id'),
            brojPolaganja: (int) $request->input('brojPolaganja', 1),
            datum: $request->input('datum'),
            tipPrijaveId: $request->filled('tipPrijave_id') ? (int) $request->input('tipPrijave_id') : null,
        );
    }

    public function toArray(): array
    {
        return [
            'kandidat_id' => $this->kandidatId,
            'predmet_id' => $this->predmetId,
            'profesor_id' => $this->profesorId,
            'rok_id' => $this->rokId,
            'brojPolaganja' => $this->brojPolaganja,
            'datum' => $this->datum,
            'tipPrijave_id' => $this->tipPrijaveId,
        ];
    }
}
