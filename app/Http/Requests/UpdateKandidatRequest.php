<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKandidatRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'ime' => 'sometimes|string|max:50',
            'prezimeKandidata' => 'sometimes|string|max:50',
            'brojIndeksa' => 'sometimes|string|max:20|unique:kandidat,brojIndeksa,' . $this->kandidat->id,
            'studijskiProgram_id' => 'sometimes|exists:studijski_program,id',
            'godinaStudija_id' => 'sometimes|exists:godina_studija,id',
            'statusUpisa_id' => 'sometimes|exists:status_studiranja,id',
            'skolskaGodinaUpisa_id' => 'sometimes|exists:skolska_god_upisa,id',
            'email' => 'nullable|email',
            'jmbg' => 'nullable|string|max:13',
        ];
    }
}
