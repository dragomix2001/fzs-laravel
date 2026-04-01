<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDiplomskiPolaganjeRequest extends FormRequest
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
            'profesor_id_predsednik' => 'required',
            'profesor_id_clan' => 'required',
            'nazivTeme' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'predmet_id.required' => 'Унесите ime предмета!',
            'profesor_id.required' => 'Унесите ime МЕНТОРА!',
            'profesor_id_predsednik.required' => 'Унесите ime председника комисије!',
            'profesor_id_clan.required' => 'Унесите ime члана комисије!',
            'nazivTeme.required' => 'Унесите назив теме!',
        ];
    }
}
