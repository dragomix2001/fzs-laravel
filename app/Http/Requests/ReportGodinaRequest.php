<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportGodinaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'godina' => ['required', 'integer'],
        ];
    }
}
