<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'agent_id' => 'required|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'agent_id.required' => 'L\'agent est requis.',
            'agent_id.integer'  => 'L\'ID de l\'agent doit être un entier.',
        ];
    }
}
