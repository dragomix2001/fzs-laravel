<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocumentReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'notes' => ['nullable', 'string', 'max:2000'],
        ];

        if ($this->routeIs('kandidat.documents.reject', 'kandidat.documents.needs-revision')) {
            $rules['notes'] = ['required', 'string', 'max:2000'];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('notes') && is_string($this->input('notes'))) {
            $this->merge([
                'notes' => trim($this->input('notes')),
            ]);
        }
    }
}
