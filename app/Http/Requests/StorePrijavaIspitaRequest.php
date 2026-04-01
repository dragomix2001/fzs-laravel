<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrijavaIspitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kandidat_id' => 'unique_with:prijava_ispita,predmet_id,rok_id',
        ];
    }

    public function messages(): array
    {
        return [
            'kandidat_id.unique_with' => 'Дошло је до грешке. Проверите да ли је студент већ пријавио тражени испит у траженом року.',
        ];
    }
}
