<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiplomskiTemaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kandidat_id' => 'unique_with:diplomski_prijava_teme,tipStudija_id',
            'predmet_id' => 'required',
            'profesor_id' => 'required',
            'nazivTeme' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'kandidat_id.unique_with' => 'Дошло је до грешке. Проверите да ли је студент већ пријавио тему завршног рада.',
            'predmet_id.required' => 'Унесите ime предмета!',
            'profesor_id.required' => 'Унесите ime ментора!',
            'nazivTeme.required' => 'Унесите назив теме!',
        ];
    }
}
