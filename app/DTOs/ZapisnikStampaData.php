<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class ZapisnikStampaData
{
    public function __construct(
        public int $zapisnikId,
        public ?string $predmet,
        public ?string $rok,
        public ?string $profesor
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            zapisnikId: (int) $request->input('id'),
            predmet: $request->input('predmet'),
            rok: $request->input('rok'),
            profesor: $request->input('profesor')
        );
    }
}
