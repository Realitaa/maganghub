<?php

namespace App\Http\Requests\Templates;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class StoreTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('manage-templates');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:docx', 'max:5120'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'File template wajib diunggah.',
            'file.file' => 'Berkas harus berupa file.',
            'file.mimes' => 'Format file template harus berupa berkas Word (.docx).',
            'file.max' => 'Ukuran file template tidak boleh lebih dari 5 MB.',
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
