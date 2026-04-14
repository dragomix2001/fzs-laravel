<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRasporedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'predmet_id' => 'required|exists:predmet,id',
            'profesor_id' => 'required|exists:profesor,id',
            'studijski_program_id' => 'required|exists:studijski_program,id',
            'godina_studija_id' => 'required|exists:godina_studija,id',
            'semestar_id' => 'required|exists:semestar,id',
            'skolska_godina_id' => 'required|exists:skolska_god_upisa,id',
            'oblik_nastave_id' => 'required|exists:oblik_nastave,id',
            'dan' => 'required|integer|min:1|max:7',
            'vreme_od' => 'required',
            'vreme_do' => 'required|after:vreme_od',
            'prostorija' => 'nullable|string|max:50',
            'grupa' => 'nullable|string|max:50',
        ];
    }
}
