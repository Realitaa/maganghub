<?php

namespace App\Http\Requests\Settings;

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = $this->profileRules($this->user()->id);

        if ($this->user()->role === 'student') {
            $rules = array_merge($rules, [
                'nim' => ['required', 'string', Rule::unique('users')->ignore($this->user()->id)],
                'phone' => ['required', 'string', 'max:20'],
                'address' => ['required', 'string'],
                'gender' => ['required', 'in:L,P'],
                'semester' => ['required', 'integer', 'min:1', 'max:14'],
            ]);
        }

        return $rules;
    }
}
