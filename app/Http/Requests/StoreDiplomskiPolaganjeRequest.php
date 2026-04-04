<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDiplomskiPolaganjeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kandidat_id' => [
                'required',
                Rule::unique('diplomski_polaganje', 'kandidat_id')->where(function ($query) {
                    return $query->where('tipStudija_id', $this->input('tipStudija_id'));
                }),
            ],
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
            'kandidat_id.unique_with' => 'Дошло је до грешке. Проверите да ли је студент већ пријавио полагање завршног рада.',
            'predmet_id.required' => 'Унесите ime предмета!',
            'profesor_id.required' => 'Унесите ime МЕНТОРА!',
            'profesor_id_predsednik.required' => 'Унесите ime председника комисије!',
            'profesor_id_clan.required' => 'Унесите ime члана комисије!',
            'nazivTeme.required' => 'Унесите назив теме!',
        ];
    }
}
