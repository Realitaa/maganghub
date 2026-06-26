<?php

namespace App\Http\Requests\Submissions;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class UploadResponseLetterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $submission = $this->route('submission');

        return Gate::allows('uploadResponse', $submission);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,docx,png,jpg,jpeg', 'max:2048'],
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
            'file.required' => 'Surat balasan wajib diunggah.',
            'file.file' => 'Berkas yang diunggah harus berupa file.',
            'file.mimes' => 'Format file surat balasan harus berupa PDF, DOCX, PNG, JPG, atau JPEG.',
            'file.max' => 'Ukuran file surat balasan tidak boleh lebih dari 2 MB.',
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
