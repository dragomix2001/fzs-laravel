<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreObavestenjeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'naslov' => 'required|string|max:255',
            'sadrzaj' => 'required',
            'tip' => 'required|string|max:50',
            'aktivan' => 'boolean',
            'datum_objave' => 'required',
            'datum_isteka' => 'nullable|after:datum_objave',
            'posalji_email' => 'boolean',
        ];
    }
}
