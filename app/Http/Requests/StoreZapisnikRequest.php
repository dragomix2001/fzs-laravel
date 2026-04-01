<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreZapisnikRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'odabir' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'odabir.required' => 'Нисте одабрали студенте за полагање испита!',
        ];
    }
}
