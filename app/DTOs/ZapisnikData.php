<?php

namespace App\DTOs;

use Illuminate\Http\Request;

readonly class ZapisnikData
{
    public function __construct(
        public int $predmetId,
        public int $profesorId,
        public int $rokId,
        public ?string $datum,
        public ?string $datum2 = null,
        public ?string $vreme = null,
        public ?string $ucionica = null,
        public ?int $prijavaIspitaId = null,
        public array $studentiIds = [],
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            predmetId: (int) $request->input('predmet_id'),
            profesorId: (int) $request->input('profesor_id'),
            rokId: (int) $request->input('rok_id'),
            datum: $request->input('datum'),
            datum2: $request->input('datum2'),
            vreme: $request->input('vreme'),
            ucionica: $request->input('ucionica'),
            prijavaIspitaId: $request->filled('prijavaIspita_id') ? (int) $request->input('prijavaIspita_id') : null,
            studentiIds: $request->input('odabir', []),
        );
    }

    public function toArray(): array
    {
        return [
            'predmet_id' => $this->predmetId,
            'profesor_id' => $this->profesorId,
            'rok_id' => $this->rokId,
            'datum' => $this->datum,
            'datum2' => $this->datum2,
            'vreme' => $this->vreme,
            'ucionica' => $this->ucionica,
            'prijavaIspita_id' => $this->prijavaIspitaId,
        ];
    }
}
