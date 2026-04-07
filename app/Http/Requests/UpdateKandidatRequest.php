<?php

namespace App\Http\Requests;

use App\Models\Kandidat;
use Illuminate\Foundation\Http\FormRequest;

class UpdateKandidatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $kandidat = Kandidat::find($this->route('id'));

        if ($kandidat && $kandidat->brojIndeksa != $this->input('brojIndeksa')) {
            return [
                'brojIndeksa' => 'unique:kandidat',
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'brojIndeksa.unique' => 'Број индекса мора бити уникатан. Већ постоји такав запис у бази.',
        ];
    }
}
