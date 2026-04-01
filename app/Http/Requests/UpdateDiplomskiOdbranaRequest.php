<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDiplomskiOdbranaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'predmet_id' => 'required',
            'temu_odobrio_profesor_id' => 'required',
            'nazivTeme' => 'required',
            'odbranu_odobrio_profesor_id' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'predmet_id.required' => 'Унесите ime предмета!',
            'temu_odobrio_profesor_id.required' => 'Унесите ime професора који одобрава ТЕМУ!',
            'nazivTeme.required' => 'Унесите назив теме!',
            'odbranu_odobrio_profesor_id.required' => 'Унесите ime професора који одобрава ОДБРАНУ!',
        ];
    }
}
