<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$this->route('user')->id,
            'role' => 'required|in:admin,professor,student',
            'password' => 'nullable|string|min:8|confirmed',
        ];
    }
}
