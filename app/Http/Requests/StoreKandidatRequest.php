<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKandidatRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'ime' => 'required|string|max:50',
            'prezimeKandidata' => 'required|string|max:50',
            'brojIndeksa' => 'required|string|max:20|unique:kandidat',
            'studijskiProgram_id' => 'required|exists:studijski_program,id',
            'godinaStudija_id' => 'required|exists:godina_studija,id',
            'statusUpisa_id' => 'required|exists:status_studiranja,id',
            'skolskaGodinaUpisa_id' => 'required|exists:skolska_god_upisa,id',
            'email' => 'nullable|email',
            'jmbg' => 'nullable|string|max:13',
        ];
    }
}
