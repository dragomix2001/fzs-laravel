<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrijavaIspitaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'kandidat_id' => 'required|exists:kandidat,id',
            'predmet_id' => 'required|exists:predmet,id',
            'profesor_id' => 'required|exists:profesor,id',
            'rok_id' => 'required|exists:ispitni_rok,id',
            'brojPolaganja' => 'required|integer|min:1',
            'datum' => 'nullable|date',
        ];
    }
}
