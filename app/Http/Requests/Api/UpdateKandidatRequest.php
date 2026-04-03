<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKandidatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'jmbg' => ['sometimes', 'string'],
            'krsnaSlava_id' => ['sometimes', 'integer'],
            'uspehSrednjaSkola_id' => ['sometimes', 'integer'],
            'opstiUspehSrednjaSkola_id' => ['sometimes', 'integer'],
            'skolskaGodinaUpisa_id' => ['sometimes', 'integer'],
            'indikatorAktivan' => ['sometimes', 'integer'],
            'studijskiProgram_id' => ['sometimes', 'integer'],
            'tipStudija_id' => ['sometimes', 'integer'],
            'godinaStudija_id' => ['sometimes', 'integer'],
            'mesto_id' => ['sometimes', 'integer'],
        ];
    }
}
