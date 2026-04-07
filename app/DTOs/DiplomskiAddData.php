<?php

namespace App\DTOs;

use Illuminate\Http\Request;

readonly class DiplomskiAddData
{
    public function __construct(
        public int $kandidatId,
        public ?int $predmetId,
        public ?string $naziv,
        public ?int $mentorId,
        public ?int $predsednikId,
        public ?int $clanId,
        public ?string $ocenaOpis,
        public ?float $ocenaBroj,
        public ?string $datumPrijave,
        public ?string $datumOdbrane,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            kandidatId: (int) ($request->input('kandidat_id') ?? $request->input('id')),
            predmetId: $request->filled('predmet_id') ? (int) $request->input('predmet_id') : ($request->filled('predmet') ? (int) $request->input('predmet') : null),
            naziv: $request->input('naziv') ?? $request->input('tema'),
            mentorId: $request->filled('mentor_id') ? (int) $request->input('mentor_id') : ($request->filled('mentor') && is_numeric($request->input('mentor')) ? (int) $request->input('mentor') : null),
            predsednikId: $request->filled('predsednik_id') ? (int) $request->input('predsednik_id') : null,
            clanId: $request->filled('clan_id') ? (int) $request->input('clan_id') : null,
            ocenaOpis: $request->input('ocenaOpis'),
            ocenaBroj: $request->filled('ocenaBroj') ? (float) str_replace(',', '.', (string) $request->input('ocenaBroj')) : null,
            datumPrijave: $request->input('datumPrijave'),
            datumOdbrane: $request->input('datumOdbrane')
        );
    }
}
