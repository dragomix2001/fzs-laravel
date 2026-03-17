<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KandidatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ime' => $this->imeKandidata,
            'prezime' => $this->prezimeKandidata,
            'broj_indeksa' => $this->brojIndeksa,
            'email' => $this->email,
            'telefon' => $this->telefon,
            'datum_rodjenja' => $this->datumRodjenja,
            'jmbg' => $this->jmbg,
            'adresa' => $this->adresa,
            'status' => $this->statusUpisa_id,
            'studijski_program' => $this->whenLoaded('program', function () {
                return [
                    'id' => $this->program->id,
                    'naziv' => $this->program->naziv,
                ];
            }),
            'godina_upisa' => $this->whenLoaded('godinaUpisa', function () {
                return [
                    'id' => $this->godinaUpisa->id,
                    'naziv' => $this->godinaUpisa->naziv,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
