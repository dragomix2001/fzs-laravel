<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class NastavniPlanData
{
    public function __construct(
        public int $predmetId,
        public int $programId,
        public int $godinaId
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            predmetId: (int) $request->input('predmet'),
            programId: (int) $request->input('program'),
            godinaId: (int) $request->input('godina')
        );
    }
}
