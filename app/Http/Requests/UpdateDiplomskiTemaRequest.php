<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDiplomskiTemaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'predmet_id' => 'required',
            'profesor_id' => 'required',
            'nazivTeme' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'predmet_id.required' => 'Унесите ime предмета!',
            'profesor_id.required' => 'Унесите ime ментора!',
            'nazivTeme.required' => 'Унесите назив теме!',
        ];
    }
}
