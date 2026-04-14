<?php

namespace App\DTOs;

use Illuminate\Http\Request;

/**
 * DTO for kandidat creation/update core data.
 *
 * Captures the key fields needed for storeKandidat() and updateKandidat()
 * in KandidatService. File uploads (imageUpload, pdfUpload) are handled
 * separately via the Request object in the service.
 */
readonly class KandidatData
{
    public function __construct(
        public string $ime,
        public string $prezime,
        public string $JMBG,
        public int $studijskiProgramId,
        public int $tipStudijaId,
        public ?string $brojIndeksa = null,
        public ?int $godinaStudijaId = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            ime: $request->input('ImeKandidata'),
            prezime: $request->input('PrezimeKandidata'),
            JMBG: $request->input('JMBG'),
            studijskiProgramId: (int) $request->input('StudijskiProgram'),
            tipStudijaId: (int) $request->input('TipStudija', 1),
            brojIndeksa: $request->input('BrojIndeksa') ?: $request->input('brojIndeksa'),
            godinaStudijaId: $request->filled('GodinaStudija') ? (int) $request->input('GodinaStudija') : null,
        );
    }

    public function toArray(): array
    {
        return [
            'imeKandidata' => $this->ime,
            'prezimeKandidata' => $this->prezime,
            'jmbg' => $this->JMBG,
            'studijskiProgram_id' => $this->studijskiProgramId,
            'tipStudija_id' => $this->tipStudijaId,
            'brojIndeksa' => $this->brojIndeksa,
            'godinaStudija_id' => $this->godinaStudijaId,
        ];
    }
}
