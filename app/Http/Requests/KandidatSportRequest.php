<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KandidatSportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sport' => ['required', 'integer'],
            'klub' => ['required', 'string', 'max:255'],
            'uzrast' => ['required', 'string', 'max:255'],
            'godine' => ['required'],
        ];
    }
}
