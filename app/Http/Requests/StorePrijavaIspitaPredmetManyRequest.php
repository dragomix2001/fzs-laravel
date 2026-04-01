<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrijavaIspitaPredmetManyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'odabir' => 'required',
            'profesor_id' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'odabir.required' => 'Нисте одабрали ниједног студента за пријаву испита!',
            'profesor_id.required' => 'Нисте одабрали професора за пријаву испита!',
        ];
    }
}
