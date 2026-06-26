<?php

namespace App\Http\Requests\Submissions;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CompanyDecisionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $submission = $this->route('submission');

        return Gate::allows('approve', $submission);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', 'string', 'in:all_accepted,all_rejected,partially_accepted'],
            'member_decisions' => ['nullable', 'array'],
            'new_leader_id' => ['nullable', 'integer', 'exists:users,id'],
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
