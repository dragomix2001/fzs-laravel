<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKandidatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->input('page') == 1) {
            return [
                'JMBG' => 'unique:kandidat|required',
            ];
        }

        if ($this->input('page') == 2) {
            return [
                'documentUploadsPrva.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'documentUploadsDruga.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute је обавезно поље.',
            'JMBG.unique' => 'ЈМБГ мора бити уникатан. Већ постоји такав запис у бази.',
            'JMBG.max' => 'ЈМБГ не може имати више од 13 цифара.',
        ];
    }
}
