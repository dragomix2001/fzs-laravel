<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMasterKandidatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'JMBG' => 'unique:kandidat|required',
        ];
    }

    public function messages(): array
    {
        return [
            'JMBG.required' => 'ЈМБГ је обавезно поље.',
            'JMBG.unique' => 'ЈМБГ мора бити уникатан. Већ постоји такав запис у бази.',
            'JMBG.max' => 'ЈМБГ не може имати више од 13 цифара.',
        ];
    }
}
