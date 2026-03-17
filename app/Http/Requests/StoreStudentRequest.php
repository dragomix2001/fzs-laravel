<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'imeKandidata' => 'required|string|max:255',
            'prezimeKandidata' => 'required|string|max:255',
            'jmbg' => 'required|string|size:13|unique:kandidat,jmbg',
            'datumRodjenja' => 'required|date',
            'mestoRodjenja_id' => 'required|integer|exists:opstina,id',
            'adresa' => 'required|string|max:255',
            'telefon' => 'required|string|max:20',
            'email' => 'required|email|unique:kandidat,email',
            'studijskiProgram_id' => 'required|integer|exists:studijski_program,id',
            'skolskaGodinaUpisa_id' => 'required|integer|exists:skolska_god_upisa,id',
            'godinaStudija_id' => 'required|integer|exists:godina_studija,id',
            'statusUpisa_id' => 'required|integer|exists:status_godine,id',
        ];
    }
}
