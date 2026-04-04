<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePrijavaIspitaRequest extends FormRequest
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
                Rule::unique('prijava_ispita', 'kandidat_id')->where(function ($query) {
                    return $query
                        ->where('predmet_id', $this->input('predmet_id'))
                        ->where('rok_id', $this->input('rok_id'));
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'kandidat_id.unique_with' => 'Дошло је до грешке. Проверите да ли је студент већ пријавио тражени испит у траженом року.',
        ];
    }
}
