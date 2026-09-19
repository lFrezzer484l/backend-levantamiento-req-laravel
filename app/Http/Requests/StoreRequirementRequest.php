<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequirementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requester' => 'required|string|max:100',
            'email' => 'required|email',
            'description' => 'required|string',
            'requirement_type' => 'required|string|max:50',
            'priority' => 'required|string|max:20',
            'titulo' => 'nullable|string|max:20',
        ];
    }
}
