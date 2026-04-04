<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDiplomskiOdbranaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kandidat_id' => [
                'required',
                Rule::unique('diplomski_prijava_odbrane', 'kandidat_id')->where(function ($query) {
                    return $query->where('tipStudija_id', $this->input('tipStudija_id'));
                }),
            ],
            'predmet_id' => 'required',
            'temu_odobrio_profesor_id' => 'required',
            'nazivTeme' => 'required',
            'odbranu_odobrio_profesor_id' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'kandidat_id.unique_with' => 'Дошло је до грешке. Проверите да ли је студент већ пријавио одбрану завршног рада.',
            'predmet_id.required' => 'Унесите ime предмета!',
            'temu_odobrio_profesor_id.required' => 'Унесите ime професора који одобрава ТЕМУ!',
            'nazivTeme.required' => 'Унесите назив теме!',
            'odbranu_odobrio_profesor_id.required' => 'Унесите ime професора који одобрава ОДБРАНУ!',
        ];
    }
}
