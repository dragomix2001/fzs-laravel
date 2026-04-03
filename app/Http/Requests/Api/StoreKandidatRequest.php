<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreKandidatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'jmbg' => ['required', 'string'],
            'krsnaSlava_id' => ['required', 'integer'],
            'uspehSrednjaSkola_id' => ['required', 'integer'],
            'opstiUspehSrednjaSkola_id' => ['required', 'integer'],
            'skolskaGodinaUpisa_id' => ['required', 'integer'],
            'indikatorAktivan' => ['required', 'integer'],
            'studijskiProgram_id' => ['required', 'integer'],
            'tipStudija_id' => ['required', 'integer'],
            'godinaStudija_id' => ['required', 'integer'],
            'mesto_id' => ['required', 'integer'],
        ];
    }
}
