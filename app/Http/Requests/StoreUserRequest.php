<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->input('student_class_id') === 'none') {
            $this->merge([
                'student_class_id' => null,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $role = $this->input('role');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', Rule::in(['administrator', 'operator', 'student'])],
            'gender' => ['nullable', Rule::in(['L', 'P'])],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'student_class_id' => ['nullable', 'exists:student_classes,id'],
        ];

        if ($role === 'student') {
            $rules['nim'] = ['required', 'string', 'unique:users,nim'];
            $rules['password'] = ['nullable', 'string', 'min:8'];
        } else {
            $rules['password'] = ['required', 'string', 'min:8'];
            $rules['nim'] = ['nullable', 'string'];
        }

        return $rules;
    }
}
