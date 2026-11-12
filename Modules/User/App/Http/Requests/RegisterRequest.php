<?php

declare(strict_types=1);

namespace Modules\User\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Modules\User\App\Models\User;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique((new User)->getTable(), 'email')],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
            'marketing_opt_in' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $name = trim((string) $this->input('name', ''));

        if ($name === '') {
            $name = trim($this->string('first_name')->toString().' '.$this->string('last_name')->toString());
        }

        $this->merge([
            'name' => preg_replace('/\s+/', ' ', $name),
            'email' => strtolower(trim((string) $this->input('email', ''))),
        ]);
    }

    public function messages(): array
    {
        return [
            'terms.accepted' => 'Please tick the box to agree to the terms and privacy policy.',
        ];
    }

    public function fullName(): string
    {
        return $this->string('name')->toString();
    }
}
