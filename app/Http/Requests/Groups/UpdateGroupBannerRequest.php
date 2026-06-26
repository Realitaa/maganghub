<?php

namespace App\Http\Requests\Groups;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class UpdateGroupBannerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $group = $this->route('group');

        return Gate::allows('updateBanner', $group);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => ['required', 'file', 'mimes:webp,png,jpg,jpeg', 'max:3072'],
            'og_image' => ['nullable', 'file', 'mimes:webp,png,jpg,jpeg', 'max:512'],
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        $message = collect($validator->errors()->all())->first();

        Inertia::flash('toast', [
            'type' => 'error',
            'message' => $message,
        ]);

        parent::failedValidation($validator);
    }
}
